<?php

namespace App\Models;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\IdentityVerification;
use App\Services\IdentityVerificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class AppUser extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens, HasFactory;

    /** app_users has no remember_token column (the app uses API tokens). */
    public function getRememberTokenName()
    {
        return '';
    }

    public const SELFIE_DIR = 'upload/selfies';
    public const IDENTITY_DIR = 'upload/identities';

    // identity_status values; null means never verified
    public const IDENTITY_VERIFIED = 'verified';
    public const IDENTITY_PENDING = 'pending';

    protected $hidden = [
        'password', 'shufti_reference', 'vcode', 'reset_token', 'reset_otp_hash', 'reset_otp_expires_at',
        'reset_otp_sent_at', 'reset_otp_attempts', 'reset_token_expires_at',
    ];

    protected $appends = ['is_verified'];

    protected $casts = [
        'identity_verified_at' => 'datetime',
        'reset_otp_expires_at' => 'datetime',
        'reset_otp_sent_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
    ];

    /**
     * Passwords are always stored hashed: a plain value is hashed on the way
     * in, an existing hash (from hash-passwords or a rehash) is kept as is.
     */
    public function setPasswordAttribute($value): void
    {
        $value = (string) $value;
        $this->attributes['password'] = self::isHashed($value) ? $value : Hash::make($value);
    }

    public static function isHashed(?string $value): bool
    {
        return $value !== null && $value !== '' && password_get_info($value)['algoName'] !== 'unknown';
    }

    /**
     * Checks a password against the stored one. Accounts not yet migrated
     * still hold the plain text; a match upgrades them to a hash on the spot.
     */
    public function checkPassword(?string $plain): bool
    {
        $plain = (string) $plain;
        $stored = (string) ($this->attributes['password'] ?? '');
        if ($plain === '' || $stored === '') {
            return false;
        }
        if (self::isHashed($stored)) {
            if (!Hash::check($plain, $stored)) {
                return false;
            }
            if (Hash::needsRehash($stored)) {
                $this->password = $plain;
                $this->saveQuietly();
            }
            return true;
        }
        if (!hash_equals($stored, $plain)) {
            return false;
        }
        $this->password = $plain;
        $this->saveQuietly();
        return true;
    }

    /**
     * What other users may see (the carrier card on an order): no email,
     * phone, documents, wallet or referral code.
     */
    public function publicProfile(): array
    {
        $rating = Rating::where('user_id', $this->id)->avg('rating');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selfie' => $this->selfie,
            'role' => 'carrier',
            'is_verified' => $this->is_verified,
            'identity_status' => $this->is_verified ? self::IDENTITY_VERIFIED : null,
            'carried_packages_count' => ParcelOrder::where('carrier_id', $this->id)->count(),
            'packages_count' => ParcelOrder::where('user_id', $this->id)->count(),
            'average_rating' => $rating ? round($rating, 2) : null,
            'ratings_count' => Rating::where('user_id', $this->id)->count(),
            'trees_saved' => $this->trees_saved,
            'created_at' => $this->created_at,
        ];
    }

    /** Shufti or the admin accepted the user's selfie and ID. */
    public function getIsVerifiedAttribute(): bool
    {
        return ($this->attributes['identity_status'] ?? null) === self::IDENTITY_VERIFIED;
    }

    /**
     * Moves an uploaded selfie into place and returns the file name.
     * Pass $shrink = false to keep the full-size file.
     */
    public static function storeSelfie(UploadedFile $file, bool $shrink = true): string
    {
        $name = Str::random(40) . '_selfie.' . self::safeExtension($file, false);
        $file->move(self::SELFIE_DIR, $name);
        if ($shrink) {
            self::shrink(self::SELFIE_DIR . '/' . $name);
        }
        return $name;
    }

    /** Moves an uploaded ID (photo or PDF) into place; see [storeSelfie]. */
    public static function storeIdentity(UploadedFile $file, bool $shrink = true): string
    {
        $name = Str::random(40) . '_identity.' . self::safeExtension($file, true);
        $file->move(self::IDENTITY_DIR, $name);
        if ($shrink) {
            self::shrink(self::IDENTITY_DIR . '/' . $name);
        }
        return $name;
    }

    /**
     * The extension to store an upload under, from its real content (never
     * the name the client sent). Anything that is not a picture (or a PDF
     * for ID documents) is refused.
     */
    public static function safeExtension(UploadedFile $file, bool $allowPdf): string
    {
        $allowed = ['jpg', 'jpeg', 'png', 'heic', 'heif', 'webp'];
        if ($allowPdf) {
            $allowed[] = 'pdf';
        }
        $ext = strtolower((string) $file->guessExtension());
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        if (!$file->isValid() || !in_array($ext, $allowed, true) || $file->getSize() > 15 * 1024 * 1024) {
            throw new \App\Exceptions\InvalidUploadException('Please upload a photo (JPG, PNG or HEIC)' . ($allowPdf ? ' or a PDF' : '') . '.');
        }
        return $ext;
    }

    /** Deletes photos stored for an attempt that did not create or update an account. */
    public static function discardUploads(?string $selfie, ?string $identity): void
    {
        foreach ([[self::SELFIE_DIR, $selfie], [self::IDENTITY_DIR, $identity]] as [$dir, $name]) {
            if ($name && is_file($dir . '/' . $name)) {
                @unlink($dir . '/' . $name);
            }
        }
    }

    /** Resize to 800 px wide and optimise; any failure keeps the original file. */
    private static function shrink(string $path): void
    {
        try {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf') {
                Image::load($path)->width(800)->save();
            }
            OptimizerChainFactory::create()->optimize($path);
        } catch (\Throwable $e) {
            // keep the moved original
        }
    }

    /** Photo verification refused while the admin requires the live check. */
    public static function liveRequiredError(): array
    {
        return [
            'msg' => 'error', 'reason' => 'identity_live_required',
            'error' => 'Please update CarryOn to the latest version to verify your identity.',
        ];
    }

    /** The error body the app maps to a localized identity message. */
    public static function identityError(IdentityVerification $attempt): array
    {
        return match ($attempt->status) {
            IdentityVerification::DECLINED => [
                'msg' => 'error', 'reason' => 'identity_declined',
                'error' => 'Identity verification failed. Please retake your photos and try again.',
            ],
            IdentityVerification::INVALID => [
                'msg' => 'error', 'reason' => 'identity_invalid', 'detail' => $attempt->message,
                'error' => 'We could not read your photos. Please retake them in good light and try again.',
            ],
            default => [
                'msg' => 'error', 'reason' => 'identity_unavailable',
                'error' => 'Identity verification is unavailable right now. Please try again in a few minutes.',
            ],
        };
    }

    // Sign up
    public function signup($data)
    {
        // Always normalize email to lowercase
        $email = strtolower($data['email']);
    
        $emailExists = AppUser::where('email', $email)->exists();
        $phoneExists = AppUser::where('phone', $data['phone'])->exists();
    
        if ($emailExists) {
            return ['msg' => 'error', 'error' => 'Oops! This email already exists.'];
        }
    
        if ($phoneExists) {
            return ['msg' => 'error', 'error' => 'Oops! This phone number is already in use.'];
        }
    
        if (isset($data['rcode'])) {
            $chkCode = AppUser::where('rcode', $data['rcode'])->first();
    
            if (!isset($chkCode->id)) {
                return ['msg' => 'error', 'error' => 'Oops! This referral code is not valid.'];
            }
        } else {
            $chkCode = null;
        }
    
        // No identity check at signup: the account verifies afterwards from
        // the app (Shufti live page or manual review) and cannot send,
        // receive or carry until then (EnsureIdentityVerified).
        $selfie = isset($data['selfie']) ? self::storeSelfie($data['selfie']) : null;
        // full size: the admin has to be able to read it
        $identity = isset($data['identity']) ? self::storeIdentity($data['identity'], false) : null;

        $add = new AppUser;
        $add->name = ucwords(strtolower($data['name']));
        $add->email = $email; // store lowercase
        $add->phone = $data['phone'];
        $add->password = $data['password'];
        $add->role = 1;
        $add->country = $data['country'] ?? null;
        $add->city = $data['city'] ?? null;
        $add->status = 1;
    
        $referralCode = strtoupper(substr($data['name'], 0, 3) . substr($data['phone'], -4));
        $add->rcode = $referralCode;
    
        if ($chkCode) {
            $user = User::find(1);
            $add->wallet = $user->point_use;
            $up = AppUser::find($chkCode->id);
            $up->wallet += $user->point_who;
            $up->save();
        }
    
        $add->selfie = $selfie;
        $add->identity = $identity;

        $add->save();

        // app builds that still send both photos at signup: in manual mode
        // they are the documents for the admin to review
        if ($selfie && $identity && IdentityVerificationService::manualMode()) {
            app(IdentityVerificationService::class)->submitManual($add, $selfie, $identity, request()->ip());
        }
    
        $add->makeHidden(['password']);
        return ['msg' => 'done', 'user' => $add->fresh()->makeHidden(['password'])];
    }

    // Login
    public function login($data)
    {
        $user = AppUser::where('email', strtolower(trim((string) ($data['email'] ?? ''))))->first();

        if (!$user || !$user->checkPassword($data['password'] ?? null)) {
            return ['msg' => 'Oops! Invalid login details'];
        }
    
        if ($user->status != 1) {
            return ['msg' => 'Your account is not verified. Please check your email for verification instructions.'];
        }
        
        $user->makeHidden(['password']);
        return ['msg' => 'done', 'user' => $user];
    }

    // Verify
    public function verify($data)
    {
        $res = AppUser::where('email', $data['email'])->first();
    
        if ($res) {
            $token = Str::random(64);
            $res->vcode = $token;
            $res->save();
    
            $verificationUrl = "https://carryonapp.com/app/verify-email/{$res->id}/{$token}";
    
            try {
                Mail::raw("Click the link to verify your email: $verificationUrl", function ($message) use ($res) {
                    $message->to($res->email)
                            ->subject("Verify Your Email");
                });
    
                return ['msg' => 'done', 'user_id' => $res->id];
            } catch (Exception $e) {
                return ['msg' => 'error', 'error' => 'Error sending email: ' . $e->getMessage()];
            }
        } else {
            return ['msg' => 'error', 'error' => 'Sorry! This email is not registered with us.'];
        }
    }
    
    // Submit Verification
    public function submitVerification($data)
    {
        $id = $data['user_id'] ?? null;
        $token = (string) ($data['token'] ?? '');

        // an empty token must never match the NULL column
        $user = strlen($token) >= 32 ? AppUser::where('id', $id)->whereNotNull('vcode')->first() : null;
        if ($user && !hash_equals((string) $user->vcode, $token)) {
            $user = null;
        }
    
        if ($user) {
            $user->status = 1;
            $user->vcode = null;
            $user->save();
    
            return ['status' => 'success', 'message' => 'Email verified successfully'];
        } else {
            return ['status' => 'error', 'message' => 'Invalid or expired link'];
        }
    }
    
    // Send reset link to email
    public function sendPasswordResetLink($data)
    {
        $user = self::where('email', $data['email'])->first();
    
        if (!$user) {
            return ['status' => 'error', 'message' => 'Email not found'];
        }
    
        $token = \Str::random(64);
        $user->reset_token = $token;
        $user->save();
    
        $resetUrl = "https://carryonapp.com/app/reset-password/{$user->id}/{$token}";
    
        try {
            \Mail::raw("Click here to reset your password: $resetUrl", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject("Reset Your Password");
            });
    
            return ['status' => 'success', 'message' => 'Reset link sent', 'user_id' => $user->id];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to send email'];
        }
    }
    
    // Submit new password using token
    public function resetPassword($data)
    {
        $token = (string) ($data['token'] ?? '');
        $user = strlen($token) >= 32 ? self::where('id', $data['user_id'] ?? null)->whereNotNull('reset_token')->first() : null;
        if ($user && (!hash_equals((string) $user->reset_token, $token)
            || !$user->reset_token_expires_at || $user->reset_token_expires_at->isPast())) {
            $user = null;
        }
    
        if (!$user) {
            return ['status' => 'error', 'message' => 'Invalid or expired token'];
        }
    
        $user->password = $data['password'];
        $user->reset_token = null;
        $user->save();
    
        return ['status' => 'success', 'message' => 'Password has been reset successfully'];
    }

    // Update User Info
    public function updateInfo($data, $id = null)
    {
        $userId = $id ?? $_GET['id'];
        $count = AppUser::where('id','!=',$userId)->where('email',$data['email'])->count();

        if($count == 0)
        {
            $add                = AppUser::find($userId);
            $add->name          = $data['name'];
            $add->email         = $data['email'];
            $add->phone         = $data['phone'];
            $add->country       = $data['country'] ?? null;;
            $add->city          = $data['city'] ?? null;;
            
            if(isset($data['password']) && $data['password'] !== '')
            {
                $add->password  = $data['password'];
            }
            
            if (isset($data['selfie'])) {
                $add->selfie = self::storeSelfie($data['selfie']);
            }
    
            if (isset($data['identity'])) {
                $add->identity = self::storeIdentity($data['identity']);
            }

            $add->save();

            $add->makeHidden(['password']);
            return ['msg' => 'done', 'user' => $add];
        }
        else
        {
            return ['msg' => 'error','error' => 'Opps! This email is already exists.'];
        }
    }

    // Get User Info
    public function userInfo() {
        $userId = $_GET['id'];
        $user = AppUser::where('id', $userId)->first();
    
        if ($user) {
            // Determine role
            $user->role = 'carrier';
    
            // Counts for packages
            $user->carried_packages_count = ParcelOrder::where('carrier_id', $user->id)->count();
            $user->packages_count = ParcelOrder::where('user_id', $user->id)->count();
    
            // Average rating and number of ratings
            $user->average_rating = Rating::where('user_id', $user->id)->avg('rating');
            $user->average_rating = $user->average_rating ? round($user->average_rating, 2) : null;
    
            $user->ratings_count = Rating::where('user_id', $user->id)->count();
            
            $user->makeHidden(['password']);
            return ['msg' => 'done', 'user' => $user];
        } else {
            return ['msg' => 'Oops! Invalid id.'];
        }
    }

    // Get All By Role
    public function getAllByRole($role)
    {
        return $this->query()
            ->where('app_users.role', $role)
    
            ->select('app_users.*')
    
            ->selectSub(function ($q) {
                $q->from('ratings')
                  ->selectRaw('ROUND(AVG(rating), 2)')
                  ->whereColumn('ratings.user_id', 'app_users.id');
            }, 'average_rating')
    
            ->selectSub(function ($q) {
                $q->from('ratings')
                  ->selectRaw('COUNT(*)')
                  ->whereColumn('ratings.user_id', 'app_users.id');
            }, 'ratings_count')
    
            ->selectSub(function ($q) {
                $q->from('parcel_orders as carried')
                  ->selectRaw('COUNT(DISTINCT carried.id)')
                  ->whereColumn('carried.carrier_id', 'app_users.id');
            }, 'carried_packages_count')
    
            ->selectSub(function ($q) {
                $q->from('parcel_orders as sent')
                  ->selectRaw('COUNT(DISTINCT sent.id)')
                  ->whereColumn('sent.user_id', 'app_users.id');
            }, 'packages_count')
    
            ->orderByDesc('app_users.id')
            ->get();
    }
    
    public function getAllByRolePaginated($role, $perPage = 50, $search = null, $status = null, $identityStatus = null)
    {
        $query = $this->where('app_users.role', $role)
            ->select('app_users.*')
            ->selectRaw('(
                SELECT ROUND(AVG(r.rating), 2)
                FROM ratings r
                WHERE r.user_id = app_users.id
            ) as average_rating')
            ->selectRaw('(
                SELECT COUNT(*)
                FROM ratings r2
                WHERE r2.user_id = app_users.id
            ) as ratings_count')
            ->selectRaw('(
                SELECT COUNT(DISTINCT po1.id)
                FROM parcel_orders po1
                WHERE po1.carrier_id = app_users.id
            ) as carried_packages_count')
            ->selectRaw('(
                SELECT COUNT(DISTINCT po2.id)
                FROM parcel_orders po2
                WHERE po2.user_id = app_users.id
            ) as packages_count')
            ->selectRaw('(
                SELECT COUNT(*)
                FROM trips t
                WHERE t.carrier_id = app_users.id
            ) as trips_count')
            ->orderBy('app_users.id', 'DESC');
    
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('app_users.name', 'LIKE', "%{$search}%")
                  ->orWhere('app_users.email', 'LIKE', "%{$search}%")
                  ->orWhere('app_users.phone', 'LIKE', "%{$search}%");
            });
        }
    
        if ($status !== null && in_array($status, [0, 1, '0', '1'], true)) {
            $query->where('app_users.status', $status);
        }

        if ($identityStatus !== null) {
            $query->where('app_users.identity_status', $identityStatus);
        }
    
        return $query->paginate($perPage);
    }

    // Got Total Orders Count for a User
    public function totalOrder($id)
    {
        return ParcelOrder::where('user_id',$id)->count();
    }
    
    
    // Delete User
    public function deleteUser($data)
    {
        $userId = $data['user_id'];
    
        // Find the user by ID
        $user = AppUser::find($userId);
    
        if (!$user) {
            return ['message' => "User not found"];
        }
    
        // Get user's parcel orders
        $orders = ParcelOrder::where('user_id', $userId)->get();
    
        // Check for ongoing orders (Assigned or Picked)
        $ongoingStatuses = ['Assigned', 'Picked'];
        $hasOngoing = $orders->contains(function ($order) use ($ongoingStatuses) {
            return in_array($order->status, $ongoingStatuses);
        });
    
        if ($hasOngoing) {
            return ['message' => 'Account cannot be deleted while having ongoing orders.'];
        }
    
        // Cancel Unassigned orders
        foreach ($orders as $order) {
            if ($order->status === 'Unassigned') {
                $order->status = 'Cancelled';
                $order->save();
            }
        }
    
        // Soft-delete the user
        $user->status = 0;
        $user->save();
    
        return ['message' => "User with ID {$userId} has been deactivated. Unassigned orders were cancelled."];
    }
    
    
    public function parcelOrderViews()
    {
        return $this->hasMany(\App\Models\ParcelOrderView::class, 'carrier_id');
    }
}
