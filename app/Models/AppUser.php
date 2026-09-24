<?php

namespace App\Models;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class AppUser extends Model
{
    use HasFactory;

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
    
        $optimizer = OptimizerChainFactory::create();
    
        // --- Selfie upload (ignore any processing/optimization errors) ---
        if (isset($data['selfie'])) {
            $selfieFile = $data['selfie'];
            $selfieFileName = time() . rand(111, 699) . '_selfie.' . $selfieFile->getClientOriginalExtension();
            $selfiePath = 'upload/selfies/' . $selfieFileName;
    
            // Move the original file first (so it’s saved even if processing fails)
            $selfieFile->move('upload/selfies', $selfieFileName);
    
            // Try to resize/optimize; ignore any errors and keep original
            try {
                // Some image libraries may throw on invalid/corrupt files
                Image::load($selfiePath)->width(800)->save();
                try {
                    $optimizer->optimize($selfiePath);
                } catch (\Throwable $e) {
                    // ignore optimizer errors
                }
            } catch (\Throwable $e) {
                // ignore image processing errors, keep the moved original
            }
    
            $add->selfie = $selfieFileName;
        }
    
        // --- Identity upload (ignore any processing/optimization errors) ---
        if (isset($data['identity'])) {
            $identityFile = $data['identity'];
            $identityFileName = time() . rand(111, 699) . '_identity.' . $identityFile->getClientOriginalExtension();
            $identityPath = 'upload/identities/' . $identityFileName;
    
            // Move the original file first
            $identityFile->move('upload/identities', $identityFileName);
    
            $ext = strtolower($identityFile->getClientOriginalExtension());
            if ($ext !== 'pdf') {
                try {
                    Image::load($identityPath)->width(800)->save();
                    try {
                        $optimizer->optimize($identityPath);
                    } catch (\Throwable $e) {
                        // ignore optimizer errors
                    }
                } catch (\Throwable $e) {
                    // ignore image processing errors, keep the moved original
                }
            } else {
                // PDFs: only try optimizer, ignore errors
                try {
                    $optimizer->optimize($identityPath);
                } catch (\Throwable $e) {
                    // ignore optimizer errors on pdf
                }
            }
    
            $add->identity = $identityFileName;
        }
    
        $add->save();
    
        $add->makeHidden(['password']);
        return ['msg' => 'done', 'user' => $add];
    }

    // Login
    public function login($data)
    {
        $user = AppUser::where('email', $data['email'])
            ->where('password', $data['password'])
            ->first();
    
        if (!$user) {
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
        $id = $data['user_id'];
        $token = $data['token'];
    
        $user = AppUser::where('id', $id)->where('vcode', $token)->first();
    
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
        $user = self::where('id', $data['user_id'])
                    ->where('reset_token', $data['token'])
                    ->first();
    
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
            
            if(isset($data['password']))
            {
                $add->password  = $data['password'];
            }
            
            $optimizer = OptimizerChainFactory::create();

            if (isset($data['selfie'])) {
                $selfieFile = $data['selfie'];
                $selfieFileName = time() . rand(111, 699) . '_selfie.' . $selfieFile->getClientOriginalExtension();
                $selfiePath = 'upload/selfies/' . $selfieFileName;
                $selfieFile->move('upload/selfies', $selfieFileName);
                Image::load($selfiePath)->width(800)->save();
                $optimizer->optimize($selfiePath);
                $add->selfie = $selfieFileName;
            }
    
            if (isset($data['identity'])) {
                $identityFile = $data['identity'];
                $identityFileName = time() . rand(111, 699) . '_identity.' . $identityFile->getClientOriginalExtension();
                $identityPath = 'upload/identities/' . $identityFileName;
                $identityFile->move('upload/identities', $identityFileName);
                Image::load($identityPath)->width(800)->save();
                $optimizer->optimize($identityPath);
                $add->identity = $identityFileName;
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
    
    public function getAllByRolePaginated($role, $perPage = 50, $search = null, $status = null)
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
