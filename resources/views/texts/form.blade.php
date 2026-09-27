<div class="card-content">
    <div class="card-body">
    
    <div class="tab-content">
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">City</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Search</label>
    <input type="text" name="city_search" class="form-control" value="{{ $data->getSData('city_search') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Locate Me</label>
    <input type="text" name="city_locate" class="form-control" value="{{ $data->getSData('city_locate') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Address</label>
    <input type="text" name="city_address" class="form-control" value="{{ $data->getSData('city_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Landmark</label>
    <input type="text" name="land_mark" class="form-control" value="{{ $data->getSData('land_mark') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Save City</label>
    <input type="text" name="city_save" class="form-control" value="{{ $data->getSData('city_save') }}">
    </div>
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Homepage, Search, Menu Item, Cart</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Search Placeholder</label>
    <input type="text" name="search" class="form-control" value="{{ $data->getSData('search') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Trending in</label>
    <input type="text" name="trend_in" class="form-control" value="{{ $data->getSData('trend_in') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Exit App</label>
    <input type="text" name="exit_app" class="form-control" value="{{ $data->getSData('exit_app') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Exit App Description</label>
    <input type="text" name="exit_app_desc" class="form-control" value="{{ $data->getSData('exit_app_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Exit App Confirm</label>
    <input type="text" name="exit_app_confirm" class="form-control" value="{{ $data->getSData('exit_app_confirm') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Previous Order Check</label>
    <input type="text" name="previous_order" class="form-control" value="{{ $data->getSData('previous_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sort By</label>
    <input type="text" name="sort_by" class="form-control" value="{{ $data->getSData('sort_by') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rating</label>
    <input type="text" name="rating" class="form-control" value="{{ $data->getSData('rating') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Nearest</label>
    <input type="text" name="nearest" class="form-control" value="{{ $data->getSData('nearest') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Arrival</label>
    <input type="text" name="new_arrival" class="form-control" value="{{ $data->getSData('new_arrival') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Km</label>
    <input type="text" name="km" class="form-control" value="{{ $data->getSData('km') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Per Person</label>
    <input type="text" name="per_person" class="form-control" value="{{ $data->getSData('per_person') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Search Page Title</label>
    <input type="text" name="search_title" class="form-control" value="{{ $data->getSData('search_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Search Result For</label>
    <input type="text" name="search_res" class="form-control" value="{{ $data->getSData('search_res') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Trending Search</label>
    <input type="text" name="trend_search" class="form-control" value="{{ $data->getSData('trend_search') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Menu Page Title</label>
    <input type="text" name="menu_title" class="form-control" value="{{ $data->getSData('menu_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Info</label>
    <input type="text" name="view_info" class="form-control" value="{{ $data->getSData('view_info') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Time</label>
    <input type="text" name="delivery_time" class="form-control" value="{{ $data->getSData('delivery_time') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Closing Time</label>
    <input type="text" name="close_time" class="form-control" value="{{ $data->getSData('close_time') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Store Close</label>
    <input type="text" name="store_close" class="form-control" value="{{ $data->getSData('store_close') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">All Items</label>
    <input type="text" name="all" class="form-control" value="{{ $data->getSData('all') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Size</label>
    <input type="text" name="select_size" class="form-control" value="{{ $data->getSData('select_size') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Required Addon Text</label>
    <input type="text" name="req_addon" class="form-control" value="{{ $data->getSData('req_addon') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add To cart</label>
    <input type="text" name="add_cart" class="form-control" value="{{ $data->getSData('add_cart') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add To Cart Message</label>
    <input type="text" name="add_cart_msg" class="form-control" value="{{ $data->getSData('add_cart_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Remove Cart Message</label>
    <input type="text" name="remove_cart_msg" class="form-control" value="{{ $data->getSData('remove_cart_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rating & Reviews</label>
    <input type="text" name="rating_review" class="form-control" value="{{ $data->getSData('rating_review') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cart Page Title</label>
    <input type="text" name="cart_title" class="form-control" value="{{ $data->getSData('cart_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Price</label>
    <input type="text" name="cart_price" class="form-control" value="{{ $data->getSData('cart_price') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cart Qty</label>
    <input type="text" name="cart_qty" class="form-control" value="{{ $data->getSData('cart_qty') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Have Discount</label>
    <input type="text" name="have_discount" class="form-control" value="{{ $data->getSData('have_discount') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Apply Here</label>
    <input type="text" name="apply_here" class="form-control" value="{{ $data->getSData('apply_here') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sub Total</label>
    <input type="text" name="sub_total" class="form-control" value="{{ $data->getSData('sub_total') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Charges</label>
    <input type="text" name="d_charges" class="form-control" value="{{ $data->getSData('d_charges') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Discount</label>
    <input type="text" name="discount" class="form-control" value="{{ $data->getSData('discount') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Payable</label>
    <input type="text" name="total_payable" class="form-control" value="{{ $data->getSData('total_payable') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Checkout</label>
    <input type="text" name="checkout" class="form-control" value="{{ $data->getSData('checkout') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Empty Cart</label>
    <input type="text" name="empty" class="form-control" value="{{ $data->getSData('empty') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Start Shopping</label>
    <input type="text" name="start_shopping" class="form-control" value="{{ $data->getSData('start_shopping') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Offer</label>
    <input type="text" name="select_offer" class="form-control" value="{{ $data->getSData('select_offer') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Offer Apply</label>
    <input type="text" name="apply" class="form-control" value="{{ $data->getSData('apply') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">No Offer</label>
    <input type="text" name="no_offer" class="form-control" value="{{ $data->getSData('no_offer') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Remove Offer</label>
    <input type="text" name="remove" class="form-control" value="{{ $data->getSData('remove') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Offer Applied</label>
    <input type="text" name="offer_applied" class="form-control" value="{{ $data->getSData('offer_applied') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cashback Message</label>
    <input type="text" name="cashback_msg" class="form-control" value="{{ $data->getSData('cashback_msg') }}">
    </div>
    
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Login,Signup & Forgot Password</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Title</label>
    <input type="text" name="login_title" class="form-control" value="{{ $data->getSData('login_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Email</label>
    <input type="text" name="login_email" class="form-control" value="{{ $data->getSData('login_email') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Password</label>
    <input type="text" name="login_password" class="form-control" value="{{ $data->getSData('login_password') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Button</label>
    <input type="text" name="login_button" class="form-control" value="{{ $data->getSData('login_button') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Forgot Password</label>
    <input type="text" name="forgot_pass" class="form-control" value="{{ $data->getSData('forgot_pass') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Dont have an account</label>
    <input type="text" name="dont_have" class="form-control" value="{{ $data->getSData('dont_have') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Now</label>
    <input type="text" name="signup_now" class="form-control" value="{{ $data->getSData('signup_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Validation</label>
    <input type="text" name="login_validation" class="form-control" value="{{ $data->getSData('login_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Error</label>
    <input type="text" name="login_error" class="form-control" value="{{ $data->getSData('login_error') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Title</label>
    <input type="text" name="signup_title" class="form-control" value="{{ $data->getSData('signup_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Description</label>
    <input type="text" name="signup_desc" class="form-control" value="{{ $data->getSData('signup_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Name</label>
    <input type="text" name="signup_name" class="form-control" value="{{ $data->getSData('signup_name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Phone</label>
    <input type="text" name="signup_phone" class="form-control" value="{{ $data->getSData('signup_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Email</label>
    <input type="text" name="signup_email" class="form-control" value="{{ $data->getSData('signup_email') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Reffer Code</label>
    <input type="text" name="rcode" class="form-control" value="{{ $data->getSData('rcode') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Password</label>
    <input type="text" name="signup_password" class="form-control" value="{{ $data->getSData('signup_password') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Button</label>
    <input type="text" name="signup_btn" class="form-control" value="{{ $data->getSData('signup_btn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Error</label>
    <input type="text" name="signup_error" class="form-control" value="{{ $data->getSData('signup_error') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Forgot Password Title</label>
    <input type="text" name="forgot_title" class="form-control" value="{{ $data->getSData('forgot_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Forgot Description</label>
    <input type="text" name="forgot_text" class="form-control" value="{{ $data->getSData('forgot_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Enter Email</label>
    <input type="text" name="enter_email" class="form-control" value="{{ $data->getSData('enter_email') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Reset</label>
    <input type="text" name="reset" class="form-control" value="{{ $data->getSData('reset') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Verify Email</label>
    <input type="text" name="verify_email" class="form-control" value="{{ $data->getSData('verify_email') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Enter OTP</label>
    <input type="text" name="enter_otp" class="form-control" value="{{ $data->getSData('enter_otp') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Verify</label>
    <input type="text" name="verify" class="form-control" value="{{ $data->getSData('verify') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Choose New Password</label>
    <input type="text" name="new_pass" class="form-control" value="{{ $data->getSData('new_pass') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Password</label>
    <input type="text" name="new_password" class="form-control" value="{{ $data->getSData('new_password') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Confirm Password</label>
    <input type="text" name="confirm_password" class="form-control" value="{{ $data->getSData('confirm_password') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Update</label>
    <input type="text" name="update_pass" class="form-control" value="{{ $data->getSData('update_pass') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">OTP Sent Message</label>
    <input type="text" name="otp_sent_msg" class="form-control" value="{{ $data->getSData('otp_sent_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">OTP Validation</label>
    <input type="text" name="otp_validation" class="form-control" value="{{ $data->getSData('otp_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Password Validation</label>
    <input type="text" name="new_pass_validation" class="form-control" value="{{ $data->getSData('new_pass_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Confirm Password Validation</label>
    <input type="text" name="confirm_pass_validation" class="form-control" value="{{ $data->getSData('confirm_pass_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Password Update Message</label>
    <input type="text" name="password_update_msg" class="form-control" value="{{ $data->getSData('password_update_msg') }}">
    </div>
    
    </div>
    
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Checkout Page</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Order Type</label>
    <input type="text" name="order_type" class="form-control" value="{{ $data->getSData('order_type') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery</label>
    <input type="text" name="delivery" class="form-control" value="{{ $data->getSData('delivery') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pickup</label>
    <input type="text" name="pickup" class="form-control" value="{{ $data->getSData('pickup') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Date Time</label>
    <input type="text" name="order_date_time" class="form-control" value="{{ $data->getSData('order_date_time') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Today</label>
    <input type="text" name="deliver_today" class="form-control" value="{{ $data->getSData('deliver_today') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Deliver Later</label>
    <input type="text" name="deliver_later" class="form-control" value="{{ $data->getSData('deliver_later') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Date</label>
    <input type="text" name="select_date" class="form-control" value="{{ $data->getSData('select_date') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Time</label>
    <input type="text" name="select_time" class="form-control" value="{{ $data->getSData('select_time') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select</label>
    <input type="text" name="select" class="form-control" value="{{ $data->getSData('select') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Personal Information</label>
    <input type="text" name="persoanl_info" class="form-control" value="{{ $data->getSData('persoanl_info') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Name</label>
    <input type="text" name="name" class="form-control" value="{{ $data->getSData('name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ $data->getSData('phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Address</label>
    <input type="text" name="address" class="form-control" value="{{ $data->getSData('address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Landmark</label>
    <input type="text" name="landmark" class="form-control" value="{{ $data->getSData('landmark') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Comment</label>
    <input type="text" name="comment" class="form-control" value="{{ $data->getSData('comment') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Payment Method</label>
    <input type="text" name="payment_method" class="form-control" value="{{ $data->getSData('payment_method') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pay on Delivery</label>
    <input type="text" name="cod" class="form-control" value="{{ $data->getSData('cod') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Stripe</label>
    <input type="text" name="stripe" class="form-control" value="{{ $data->getSData('stripe') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Razor Pay</label>
    <input type="text" name="razor" class="form-control" value="{{ $data->getSData('razor') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Stripe Payment Form Title</label>
    <input type="text" name="stripe_title" class="form-control" value="{{ $data->getSData('stripe_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Card No</label>
    <input type="text" name="card_no" class="form-control" value="{{ $data->getSData('card_no') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Exp Month</label>
    <input type="text" name="exp_month" class="form-control" value="{{ $data->getSData('exp_month') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Exp Year</label>
    <input type="text" name="exp_year" class="form-control" value="{{ $data->getSData('exp_year') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">CVV</label>
    <input type="text" name="cvv" class="form-control" value="{{ $data->getSData('cvv') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Now</label>
    <input type="text" name="book_now" class="form-control" value="{{ $data->getSData('book_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Stripe Validation Error</label>
    <input type="text" name="stripe_validation" class="form-control" value="{{ $data->getSData('stripe_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Stripe Config Error</label>
    <input type="text" name="stripe_config" class="form-control" value="{{ $data->getSData('stripe_config') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Stripe Card no Validation Error</label>
    <input type="text" name="card_no_validation" class="form-control" value="{{ $data->getSData('card_no_validation') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Success Title</label>
    <input type="text" name="apt_success" class="form-control" value="{{ $data->getSData('apt_success') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Ref. No</label>
    <input type="text" name="ref_no" class="form-control" value="{{ $data->getSData('ref_no') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Amount</label>
    <input type="text" name="total_amount" class="form-control" value="{{ $data->getSData('total_amount') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Go Back</label>
    <input type="text" name="go_back" class="form-control" value="{{ $data->getSData('go_back') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add New</label>
    <input type="text" name="address_add" class="form-control" value="{{ $data->getSData('address_add') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">No Address Message</label>
    <input type="text" name="address_msg" class="form-control" value="{{ $data->getSData('address_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">eCash Description</label>
    <input type="text" name="ecash_desc" class="form-control" value="{{ $data->getSData('ecash_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">eCash</label>
    <input type="text" name="ecash" class="form-control" value="{{ $data->getSData('ecash') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Use eCash</label>
    <input type="text" name="use_ecash" class="form-control" value="{{ $data->getSData('use_ecash') }}">
    </div>
    
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">No Running Order</label>
    <input type="text" name="no_running_order" class="form-control" value="{{ $data->getSData('no_running_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">On Going Order</label>
    <input type="text" name="on_going_order" class="form-control" value="{{ $data->getSData('on_going_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order From</label>
    <input type="text" name="order_from" class="form-control" value="{{ $data->getSData('order_from') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Placed Text</label>
    <input type="text" name="order_placed_text" class="form-control" value="{{ $data->getSData('order_placed_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Confirmed Text</label>
    <input type="text" name="order_confirmed_text" class="form-control" value="{{ $data->getSData('order_confirmed_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Assign Text</label>
    <input type="text" name="delivery_assign_text" class="form-control" value="{{ $data->getSData('delivery_assign_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order On the Way Text</label>
    <input type="text" name="order_on_way" class="form-control" value="{{ $data->getSData('order_on_way') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Cancel Text</label>
    <input type="text" name="order_cancel_text" class="form-control" value="{{ $data->getSData('order_cancel_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order No</label>
    <input type="text" name="order_no" class="form-control" value="{{ $data->getSData('order_no') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Detail</label>
    <input type="text" name="order_detail" class="form-control" value="{{ $data->getSData('order_detail') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Call</label>
    <input type="text" name="call" class="form-control" value="{{ $data->getSData('call') }}">
    </div>
    
    
    
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">My Account, My Orders</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Welcome</label>
    <input type="text" name="welcome" class="form-control" value="{{ $data->getSData('welcome') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Referral Code</label>
    <input type="text" name="ref_code" class="form-control" value="{{ $data->getSData('ref_code') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Ref Code Description</label>
    <input type="text" name="ref_code_desc" class="form-control" value="{{ $data->getSData('ref_code_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Account Title</label>
    <input type="text" name="account_title" class="form-control" value="{{ $data->getSData('account_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Orders</label>
    <input type="text" name="my" class="form-control" value="{{ $data->getSData('my') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Language Setting</label>
    <input type="text" name="lang_setting" class="form-control" value="{{ $data->getSData('lang_setting') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Change Location</label>
    <input type="text" name="change_location" class="form-control" value="{{ $data->getSData('change_location') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Back to Home</label>
    <input type="text" name="back_home" class="form-control" value="{{ $data->getSData('back_home') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Logout</label>
    <input type="text" name="logout" class="form-control" value="{{ $data->getSData('logout') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Date</label>
    <input type="text" name="order_date" class="form-control" value="{{ $data->getSData('order_date') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Status</label>
    <input type="text" name="status" class="form-control" value="{{ $data->getSData('status') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Item</label>
    <input type="text" name="item" class="form-control" value="{{ $data->getSData('item') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Quantity</label>
    <input type="text" name="qty" class="form-control" value="{{ $data->getSData('qty') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Price</label>
    <input type="text" name="price" class="form-control" value="{{ $data->getSData('price') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">No Order</label>
    <input type="text" name="no_order" class="form-control" value="{{ $data->getSData('no_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cancel Order</label>
    <input type="text" name="cancel_order" class="form-control" value="{{ $data->getSData('cancel_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cancel Order Confirmation</label>
    <input type="text" name="cancel_order_confirm" class="form-control" value="{{ $data->getSData('cancel_order_confirm') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cancel Order Confirmation Description</label>
    <input type="text" name="cancel_order_confirm_desc" class="form-control" value="{{ $data->getSData('cancel_order_confirm_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rating Title</label>
    <input type="text" name="rating_title" class="form-control" value="{{ $data->getSData('rating_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rating Description</label>
    <input type="text" name="rating_des" class="form-control" value="{{ $data->getSData('rating_des') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Submit Button</label>
    <input type="text" name="submit_btn" class="form-control" value="{{ $data->getSData('submit_btn') }}">
    </div>
    
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Other Pages & Navigation</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Menu Welcome Text</label>
    <input type="text" name="menu_welcome" class="form-control" value="{{ $data->getSData('menu_welcome') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Home</label>
    <input type="text" name="menu_home" class="form-control" value="{{ $data->getSData('menu_home') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Orders</label>
    <input type="text" name="menu_my" class="form-control" value="{{ $data->getSData('menu_my') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Account</label>
    <input type="text" name="menu_my_account" class="form-control" value="{{ $data->getSData('menu_my_account') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Language Setting</label>
    <input type="text" name="menu_lang" class="form-control" value="{{ $data->getSData('menu_lang') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Change Location</label>
    <input type="text" name="menu_location" class="form-control" value="{{ $data->getSData('menu_location') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Running Orders</label>
    <input type="text" name="running_order" class="form-control" value="{{ $data->getSData('running_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">About Us</label>
    <input type="text" name="menu_about" class="form-control" value="{{ $data->getSData('menu_about') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">FAQs</label>
    <input type="text" name="menu_faq" class="form-control" value="{{ $data->getSData('menu_faq') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Contact Us</label>
    <input type="text" name="menu_contact" class="form-control" value="{{ $data->getSData('menu_contact') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Change Language Title</label>
    <input type="text" name="lang_title" class="form-control" value="{{ $data->getSData('lang_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Update Language</label>
    <input type="text" name="update_lang" class="form-control" value="{{ $data->getSData('update_lang') }}">
    </div>
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Delivery App</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Phone</label>
    <input type="text" name="d_login_phone" class="form-control" value="{{ $data->getSData('d_login_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Home Title</label>
    <input type="text" name="d_home_title" class="form-control" value="{{ $data->getSData('d_home_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Active</label>
    <input type="text" name="d_active" class="form-control" value="{{ $data->getSData('d_active') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Offline</label>
    <input type="text" name="d_offline" class="form-control" value="{{ $data->getSData('d_offline') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">No Order</label>
    <input type="text" name="d_no_order" class="form-control" value="{{ $data->getSData('d_no_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order id</label>
    <input type="text" name="d_order_id" class="form-control" value="{{ $data->getSData('d_order_id') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">User</label>
    <input type="text" name="d_user" class="form-control" value="{{ $data->getSData('d_user') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Phone</label>
    <input type="text" name="d_phone" class="form-control" value="{{ $data->getSData('d_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Address</label>
    <input type="text" name="d_address" class="form-control" value="{{ $data->getSData('d_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Detail</label>
    <input type="text" name="d_view_detail" class="form-control" value="{{ $data->getSData('d_view_detail') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Accept Order</label>
    <input type="text" name="d_accept" class="form-control" value="{{ $data->getSData('d_accept') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Show Direction</label>
    <input type="text" name="d_show_dir" class="form-control" value="{{ $data->getSData('d_show_dir') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Items</label>
    <input type="text" name="d_order_item" class="form-control" value="{{ $data->getSData('d_order_item') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Other Info</label>
    <input type="text" name="d_other_info" class="form-control" value="{{ $data->getSData('d_other_info') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Amount</label>
    <input type="text" name="d_total_amount" class="form-control" value="{{ $data->getSData('d_total_amount') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Payment Method</label>
    <input type="text" name="d_payment_method" class="form-control" value="{{ $data->getSData('d_payment_method') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cash on Delivery</label>
    <input type="text" name="d_cod" class="form-control" value="{{ $data->getSData('d_cod') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Online Paid</label>
    <input type="text" name="d_online" class="form-control" value="{{ $data->getSData('d_online') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">eCash Paid</label>
    <input type="text" name="d_ecash_paid" class="form-control" value="{{ $data->getSData('d_ecash_paid') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Paid with eCash</label>
    <input type="text" name="d_paid_ecash" class="form-control" value="{{ $data->getSData('d_paid_ecash') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Payable</label>
    <input type="text" name="d_total_pay" class="form-control" value="{{ $data->getSData('d_total_pay') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Start Ride</label>
    <input type="text" name="d_start_ride" class="form-control" value="{{ $data->getSData('d_start_ride') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Complete Ride</label>
    <input type="text" name="d_complete_ride" class="form-control" value="{{ $data->getSData('d_complete_ride') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Confirm Status Change</label>
    <input type="text" name="d_confirm" class="form-control" value="{{ $data->getSData('d_confirm') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Delivered Start</label>
    <input type="text" name="d_order_start" class="form-control" value="{{ $data->getSData('d_order_start') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Delivered Message</label>
    <input type="text" name="d_order_delivered" class="form-control" value="{{ $data->getSData('d_order_delivered') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Account Title</label>
    <input type="text" name="d_account_title" class="form-control" value="{{ $data->getSData('d_account_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Order</label>
    <input type="text" name="d_my_order" class="form-control" value="{{ $data->getSData('d_my_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Change Password</label>
    <input type="text" name="d_change_pass" class="form-control" value="{{ $data->getSData('d_change_pass') }}">
    </div>
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Store App</h5>
    
    <div class="form-row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Dont Have Account</label>
    <input type="text" name="s_dont_have" class="form-control" value="{{ $data->getSData('s_dont_have') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Now</label>
    <input type="text" name="s_signup_now" class="form-control" value="{{ $data->getSData('s_signup_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Title</label>
    <input type="text" name="s_signup_title" class="form-control" value="{{ $data->getSData('s_signup_title') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Description</label>
    <input type="text" name="s_signup_desc" class="form-control" value="{{ $data->getSData('s_signup_desc') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Name</label>
    <input type="text" name="s_signup_name" class="form-control" value="{{ $data->getSData('s_signup_name') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Phone</label>
    <input type="text" name="s_signup_phone" class="form-control" value="{{ $data->getSData('s_signup_phone') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Address</label>
    <input type="text" name="s_signup_address" class="form-control" value="{{ $data->getSData('s_signup_address') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Password</label>
    <input type="text" name="s_signup_pass" class="form-control" value="{{ $data->getSData('s_signup_pass') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Submit Button</label>
    <input type="text" name="s_submit" class="form-control" value="{{ $data->getSData('s_submit') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Orders Overview</label>
    <input type="text" name="s_order_overview" class="form-control" value="{{ $data->getSData('s_order_overview') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Order</label>
    <input type="text" name="s_total_order" class="form-control" value="{{ $data->getSData('s_total_order') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Completed Order</label>
    <input type="text" name="s_complete_order" class="form-control" value="{{ $data->getSData('s_complete_order') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Orders</label>
    <input type="text" name="s_new_order" class="form-control" value="{{ $data->getSData('s_new_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cancelled Order</label>
    <input type="text" name="s_cancel_order" class="form-control" value="{{ $data->getSData('s_cancel_order') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery</label>
    <input type="text" name="s_delivery" class="form-control" value="{{ $data->getSData('s_delivery') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pickup</label>
    <input type="text" name="s_pickup" class="form-control" value="{{ $data->getSData('s_pickup') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Order Status</label>
    <input type="text" name="s_new_order_status" class="form-control" value="{{ $data->getSData('s_new_order_status') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Confirm Order Status</label>
    <input type="text" name="s_confirm_order_status" class="form-control" value="{{ $data->getSData('s_confirm_order_status') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Assign Status</label>
    <input type="text" name="s_delivery_assign_status" class="form-control" value="{{ $data->getSData('s_delivery_assign_status') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">On the way Status</label>
    <input type="text" name="s_on_way_status" class="form-control" value="{{ $data->getSData('s_on_way_status') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">eCash Used</label>
    <input type="text" name="s_ecash_used" class="form-control" value="{{ $data->getSData('s_ecash_used') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cancel Order</label>
    <input type="text" name="s_canceled_order" class="form-control" value="{{ $data->getSData('s_canceled_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Confirm Order</label>
    <input type="text" name="s_confirm_order" class="form-control" value="{{ $data->getSData('s_confirm_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Assign Delivery</label>
    <input type="text" name="s_assign_delivery" class="form-control" value="{{ $data->getSData('s_assign_delivery') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Delivery Boy</label>
    <input type="text" name="s_select_delivery_boy" class="form-control" value="{{ $data->getSData('s_select_delivery_boy') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Assign Button</label>
    <input type="text" name="s_assign_btn" class="form-control" value="{{ $data->getSData('s_assign_btn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Cancel Message</label>
    <input type="text" name="s_order_cancel_msg" class="form-control" value="{{ $data->getSData('s_order_cancel_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Status Message</label>
    <input type="text" name="s_order_status_msg" class="form-control" value="{{ $data->getSData('s_order_status_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Update Account Setting</label>
    <input type="text" name="s_update_account" class="form-control" value="{{ $data->getSData('s_update_account') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Charges</label>
    <input type="text" name="s_delivery_charges" class="form-control" value="{{ $data->getSData('s_delivery_charges') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Enter KM for Fix Delivery Charges</label>
    <input type="text" name="s_enter_km_fix" class="form-control" value="{{ $data->getSData('s_enter_km_fix') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Enter Amount for Fix Charges</label>
    <input type="text" name="s_enter_amount_fix" class="form-control" value="{{ $data->getSData('s_enter_amount_fix') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Amount will be charged after fix KM</label>
    <input type="text" name="s_amount_after" class="form-control" value="{{ $data->getSData('s_amount_after') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Max Delivery Area in KM</label>
    <input type="text" name="s_max_delivery" class="form-control" value="{{ $data->getSData('s_max_delivery') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Store Open/Close & Change Password</label>
    <input type="text" name="s_store_open_close" class="form-control" value="{{ $data->getSData('s_store_open_close') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Store Open</label>
    <input type="text" name="s_store_open" class="form-control" value="{{ $data->getSData('s_store_open') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Email Error</label>
    <input type="text" name="s_email_error" class="form-control" value="{{ $data->getSData('s_email_error') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Setting Updated Message</label>
    <input type="text" name="s_setting_update" class="form-control" value="{{ $data->getSData('s_setting_update') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Menu Items</label>
    <input type="text" name="s_menu_item" class="form-control" value="{{ $data->getSData('s_menu_item') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">All Items</label>
    <input type="text" name="s_all_item" class="form-control" value="{{ $data->getSData('s_all_item') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Edit</label>
    <input type="text" name="s_edit" class="form-control" value="{{ $data->getSData('s_edit') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Small Price</label>
    <input type="text" name="s_small_price" class="form-control" value="{{ $data->getSData('s_small_price') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Medium Price</label>
    <input type="text" name="s_medium_price" class="form-control" value="{{ $data->getSData('s_medium_price') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Full/Large Price</label>
    <input type="text" name="s_full_price" class="form-control" value="{{ $data->getSData('s_full_price') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Message</label>
    <input type="text" name="s_signup_msg" class="form-control" value="{{ $data->getSData('s_signup_msg') }}">
    </div>
    <div class="form-group col-md-4">
    <label for="inputEmail6">Close</label>
    <input type="text" name="close" class="form-control" value="{{ $data->getSData('close') }}">
    </div>
    </div>
    
    <h5 class="font-weight-bold mt-2 mb-1 pb-50 border-bottom">Other Info</h5>
    
    <div class="row">
    <div class="form-group col-md-4">
    <label for="inputEmail6">Time Period</label>
    <input type="text" name="time_period" class="form-control" value="{{ $data->getSData('time_period') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pay with Cash</label>
    <input type="text" name="pay_with_cash" class="form-control" value="{{ $data->getSData('pay_with_cash') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Bank Transfer</label>
    <input type="text" name="bank_transfer" class="form-control" value="{{ $data->getSData('bank_transfer') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Terms & Condition</label>
    <input type="text" name="term" class="form-control" value="{{ $data->getSData('term') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Terms & Condition Text</label>
    <input type="text" name="term_text" class="form-control" value="{{ $data->getSData('term_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Plan</label>
    <input type="text" name="my_plan" class="form-control" value="{{ $data->getSData('my_plan') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Plan Text</label>
    <input type="text" name="my_plan_text" class="form-control" value="{{ $data->getSData('my_plan_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Valid Till</label>
    <input type="text" name="valid_till" class="form-control" value="{{ $data->getSData('valid_till') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Change Plan</label>
    <input type="text" name="change_plan" class="form-control" value="{{ $data->getSData('change_plan') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Plan</label>
    <input type="text" name="select_plan" class="form-control" value="{{ $data->getSData('select_plan') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Any Notes For Payment</label>
    <input type="text" name="notes_bank" class="form-control" value="{{ $data->getSData('notes_bank') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Earning</label>
    <input type="text" name="my_earn" class="form-control" value="{{ $data->getSData('my_earn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">My Earning Text</label>
    <input type="text" name="my_earn_text" class="form-control" value="{{ $data->getSData('my_earn_text') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Completed Order</label>
    <input type="text" name="total_complete_order" class="form-control" value="{{ $data->getSData('total_complete_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Earn</label>
    <input type="text" name="total_earn" class="form-control" value="{{ $data->getSData('total_earn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">From This Month</label>
    <input type="text" name="from_month" class="form-control" value="{{ $data->getSData('from_month') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Eta</label>
    <input type="text" name="eta" class="form-control" value="{{ $data->getSData('eta') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Recommended For You</label>
    <input type="text" name="reco" class="form-control" value="{{ $data->getSData('reco') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Out of Stock</label>
    <input type="text" name="out_stock" class="form-control" value="{{ $data->getSData('out_stock') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Out of Stock Message</label>
    <input type="text" name="out_stock_msg" class="form-control" value="{{ $data->getSData('out_stock_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Out of Stock Message for Checkout</label>
    <input type="text" name="out_stock_msg_checkout" class="form-control" value="{{ $data->getSData('out_stock_msg_checkout') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Login Button</label>
    <input type="text" name="login_btn" class="form-control" value="{{ $data->getSData('login_btn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Signup Button</label>
    <input type="text" name="sign_btn" class="form-control" value="{{ $data->getSData('sign_btn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Title</label>
    <input type="text" name="delivery_title" class="form-control" value="{{ $data->getSData('delivery_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Dining Title</label>
    <input type="text" name="dinein_title" class="form-control" value="{{ $data->getSData('dinein_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Location Title</label>
    <input type="text" name="location_title" class="form-control" value="{{ $data->getSData('location_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Direction</label>
    <input type="text" name="direction" class="form-control" value="{{ $data->getSData('direction') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Contact No</label>
    <input type="text" name="contact_no" class="form-control" value="{{ $data->getSData('contact_no') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Call Now</label>
    <input type="text" name="call_now" class="form-control" value="{{ $data->getSData('call_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Timing</label>
    <input type="text" name="timing" class="form-control" value="{{ $data->getSData('timing') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Avg Per Person</label>
    <input type="text" name="avg_per" class="form-control" value="{{ $data->getSData('avg_per') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Menu</label>
    <input type="text" name="view_menu" class="form-control" value="{{ $data->getSData('view_menu') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Access Page</label>
    <input type="text" name="access_page" class="form-control" value="{{ $data->getSData('access_page') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pay With Paypal</label>
    <input type="text" name="pay_paypal" class="form-control" value="{{ $data->getSData('pay_paypal') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pay With PayStack</label>
    <input type="text" name="pay_paystack" class="form-control" value="{{ $data->getSData('pay_paystack') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Paypal Payment Faild Message</label>
    <input type="text" name="paypal_fail" class="form-control" value="{{ $data->getSData('paypal_fail') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Paypal Not Config Message</label>
    <input type="text" name="text_paypal_wrong" class="form-control" value="{{ $data->getSData('text_paypal_wrong') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delivery Charge Message</label>
    <input type="text" name="d_charge_msg" class="form-control" value="{{ $data->getSData('d_charge_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Your Location</label>
    <input type="text" name="select_location" class="form-control" value="{{ $data->getSData('select_location') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Clear</label>
    <input type="text" name="clear" class="form-control" value="{{ $data->getSData('clear') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Fast Delivery</label>
    <input type="text" name="fast_delivery" class="form-control" value="{{ $data->getSData('fast_delivery') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Choose From Category</label>
    <input type="text" name="choose_category" class="form-control" value="{{ $data->getSData('choose_category') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View All</label>
    <input type="text" name="view_all" class="form-control" value="{{ $data->getSData('view_all') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Store Around You</label>
    <input type="text" name="store_around" class="form-control" value="{{ $data->getSData('store_around') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Showing For category</label>
    <input type="text" name="showing_category" class="form-control" value="{{ $data->getSData('showing_category') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Filter By</label>
    <input type="text" name="filter_by" class="form-control" value="{{ $data->getSData('filter_by') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Footer Cart</label>
    <input type="text" name="footer_cart" class="form-control" value="{{ $data->getSData('footer_cart') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Footer Profile</label>
    <input type="text" name="footer_profile" class="form-control" value="{{ $data->getSData('footer_profile') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Your Profile</label>
    <input type="text" name="your_profile" class="form-control" value="{{ $data->getSData('your_profile') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Menu Description</label>
    <input type="text" name="menu_desc" class="form-control" value="{{ $data->getSData('menu_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Continue</label>
    <input type="text" name="conti" class="form-control" value="{{ $data->getSData('conti') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Account</label>
    <input type="text" name="view_account" class="form-control" value="{{ $data->getSData('view_account') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Search Result For</label>
    <input type="text" name="search_res" class="form-control" value="{{ $data->getSData('search_res') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">All Store that Deliver</label>
    <input type="text" name="all_store_deliver" class="form-control" value="{{ $data->getSData('all_store_deliver') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Recent Search Query</label>
    <input type="text" name="recent_search_query" class="form-control" value="{{ $data->getSData('recent_search_query') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Veg Only</label>
    <input type="text" name="veg" class="form-control" value="{{ $data->getSData('veg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Non-Veg Only</label>
    <input type="text" name="nonveg" class="form-control" value="{{ $data->getSData('nonveg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add</label>
    <input type="text" name="add" class="form-control" value="{{ $data->getSData('add') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Cooking Ins</label>
    <input type="text" name="cooking_notes" class="form-control" value="{{ $data->getSData('cooking_notes') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Write Notes</label>
    <input type="text" name="write_note" class="form-control" value="{{ $data->getSData('write_note') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Country</label>
    <input type="text" name="country" class="form-control" value="{{ $data->getSData('country') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Verify Your Email</label>
    <input type="text" name="verify_your_email" class="form-control" value="{{ $data->getSData('verify_your_email') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Verify Your Phone</label>
    <input type="text" name="verify_your_phone" class="form-control" value="{{ $data->getSData('verify_your_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Verify Code has been sent</label>
    <input type="text" name="verify_code_sent" class="form-control" value="{{ $data->getSData('verify_code_sent') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">i agree with</label>
    <input type="text" name="i_agree" class="form-control" value="{{ $data->getSData('i_agree') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Terms & Condition</label>
    <input type="text" name="terms" class="form-control" value="{{ $data->getSData('terms') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add Tip</label>
    <input type="text" name="add_tip" class="form-control" value="{{ $data->getSData('add_tip') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Tip Added</label>
    <input type="text" name="tip_added" class="form-control" value="{{ $data->getSData('tip_added') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Address & Payment Method</label>
    <input type="text" name="address_payment" class="form-control" value="{{ $data->getSData('address_payment') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Delivery Address</label>
    <input type="text" name="select_delivery_address" class="form-control" value="{{ $data->getSData('select_delivery_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Address</label>
    <input type="text" name="want_new_address" class="form-control" value="{{ $data->getSData('want_new_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Tip Amount</label>
    <input type="text" name="select_tip" class="form-control" value="{{ $data->getSData('select_tip') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pay with FlutterWave</label>
    <input type="text" name="fw" class="form-control" value="{{ $data->getSData('fw') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Start Using</label>
    <input type="text" name="start_using" class="form-control" value="{{ $data->getSData('start_using') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Use Ecash Description</label>
    <input type="text" name="ecash_use_desc" class="form-control" value="{{ $data->getSData('ecash_use_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Whatsapp Chat</label>
    <input type="text" name="w_chat" class="form-control" value="{{ $data->getSData('w_chat') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Less</label>
    <input type="text" name="view_less" class="form-control" value="{{ $data->getSData('view_less') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Get Started</label>
    <input type="text" name="get_started" class="form-control" value="{{ $data->getSData('get_started') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Email us</label>
    <input type="text" name="email_us" class="form-control" value="{{ $data->getSData('email_us') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Call Us</label>
    <input type="text" name="call_us" class="form-control" value="{{ $data->getSData('call_us') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Send Message on Whatsapp</label>
    <input type="text" name="send_msg" class="form-control" value="{{ $data->getSData('send_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Any Query</label>
    <input type="text" name="any_query" class="form-control" value="{{ $data->getSData('any_query') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Your Message</label>
    <input type="text" name="your_msg" class="form-control" value="{{ $data->getSData('your_msg') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Decline</label>
    <input type="text" name="decline" class="form-control" value="{{ $data->getSData('decline') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">You will earn</label>
    <input type="text" name="you_earn" class="form-control" value="{{ $data->getSData('you_earn') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Drop-Off</label>
    <input type="text" name="drop_off" class="form-control" value="{{ $data->getSData('drop_off') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Pickup</label>
    <input type="text" name="pickup" class="form-control" value="{{ $data->getSData('pickup') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Hotspot</label>
    <input type="text" name="hotspot" class="form-control" value="{{ $data->getSData('hotspot') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Edit</label>
    <input type="text" name="edit" class="form-control" value="{{ $data->getSData('edit') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Delete</label>
    <input type="text" name="delete" class="form-control" value="{{ $data->getSData('delete') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Add New</label>
    <input type="text" name="add_new" class="form-control" value="{{ $data->getSData('add_new') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Select Category</label>
    <input type="text" name="select_category" class="form-control" value="{{ $data->getSData('select_category') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Item Name</label>
    <input type="text" name="item_name" class="form-control" value="{{ $data->getSData('item_name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Description</label>
    <input type="text" name="description" class="form-control" value="{{ $data->getSData('description') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Item Type</label>
    <input type="text" name="item_type" class="form-control" value="{{ $data->getSData('item_type') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Both</label>
    <input type="text" name="both" class="form-control" value="{{ $data->getSData('both') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sort Order</label>
    <input type="text" name="sort_order" class="form-control" value="{{ $data->getSData('sort_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Upload Image</label>
    <input type="text" name="upload_img" class="form-control" value="{{ $data->getSData('upload_img') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">New Category Name</label>
    <input type="text" name="new_cate_name" class="form-control" value="{{ $data->getSData('new_cate_name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Opening</label>
    <input type="text" name="opening" class="form-control" value="{{ $data->getSData('opening') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Open Now</label>
    <input type="text" name="open_now" class="form-control" value="{{ $data->getSData('open_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Closed Now</label>
    <input type="text" name="closed_now" class="form-control" value="{{ $data->getSData('closed_now') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Your Cart</label>
    <input type="text" name="your_cart" class="form-control" value="{{ $data->getSData('your_cart') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Items From</label>
    <input type="text" name="item_from" class="form-control" value="{{ $data->getSData('item_from') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Invoice</label>
    <input type="text" name="invoice" class="form-control" value="{{ $data->getSData('invoice') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Invoice Desc</label>
    <input type="text" name="invoice_desc" class="form-control" value="{{ $data->getSData('invoice_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Success</label>
    <input type="text" name="success" class="form-control" value="{{ $data->getSData('success') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Order Success</label>
    <input type="text" name="order_success" class="form-control" value="{{ $data->getSData('order_success') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Check Order</label>
    <input type="text" name="check_order" class="form-control" value="{{ $data->getSData('check_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Next Step</label>
    <input type="text" name="next_step" class="form-control" value="{{ $data->getSData('next_step') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Track Order</label>
    <input type="text" name="track_order" class="form-control" value="{{ $data->getSData('track_order') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Track Desc</label>
    <input type="text" name="track_desc" class="form-control" value="{{ $data->getSData('track_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">View Order History</label>
    <input type="text" name="view_order_history" class="form-control" value="{{ $data->getSData('view_order_history') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Whatsapp Desc</label>
    <input type="text" name="whatsapp_desc" class="form-control" value="{{ $data->getSData('whatsapp_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Welcome Back</label>
    <input type="text" name="welcome_back" class="form-control" value="{{ $data->getSData('welcome_back') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Reset Here</label>
    <input type="text" name="reset_here" class="form-control" value="{{ $data->getSData('reset_here') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Shopping Continue</label>
    <input type="text" name="shop_conti" class="form-control" value="{{ $data->getSData('shop_conti') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Orders</label>
    <input type="text" name="orders" class="form-control" value="{{ $data->getSData('orders') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Parcel</label>
    <input type="text" name="parcel" class="form-control" value="{{ $data->getSData('parcel') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Total Distance</label>
    <input type="text" name="total_dis" class="form-control" value="{{ $data->getSData('total_dis') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sender Detail</label>
    <input type="text" name="sender_detail" class="form-control" value="{{ $data->getSData('sender_detail') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Receiver Detail</label>
    <input type="text" name="rec_detail" class="form-control" value="{{ $data->getSData('rec_detail') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sender Name</label>
    <input type="text" name="sender_name" class="form-control" value="{{ $data->getSData('sender_name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sender Phone</label>
    <input type="text" name="sender_phone" class="form-control" value="{{ $data->getSData('sender_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sender Address</label>
    <input type="text" name="sender_address" class="form-control" value="{{ $data->getSData('sender_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rec Name</label>
    <input type="text" name="rec_name" class="form-control" value="{{ $data->getSData('rec_name') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rec Phone</label>
    <input type="text" name="rec_phone" class="form-control" value="{{ $data->getSData('rec_phone') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rec Address</label>
    <input type="text" name="rec_address" class="form-control" value="{{ $data->getSData('rec_address') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Send Parcel</label>
    <input type="text" name="send_parcel" class="form-control" value="{{ $data->getSData('send_parcel') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Parcel Title</label>
    <input type="text" name="parcel_title" class="form-control" value="{{ $data->getSData('parcel_title') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Parcel Desc</label>
    <input type="text" name="parcel_desc" class="form-control" value="{{ $data->getSData('parcel_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">What Are you sending</label>
    <input type="text" name="what_sending" class="form-control" value="{{ $data->getSData('what_sending') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Sender</label>
    <input type="text" name="sender" class="form-control" value="{{ $data->getSData('sender') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Reciver</label>
    <input type="text" name="rec" class="form-control" value="{{ $data->getSData('rec') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Send Later</label>
    <input type="text" name="send_later" class="form-control" value="{{ $data->getSData('send_later') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Send Package</label>
    <input type="text" name="send_package" class="form-control" value="{{ $data->getSData('send_package') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Send Package Desc</label>
    <input type="text" name="send_package_desc" class="form-control" value="{{ $data->getSData('send_package_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">I Accept Terms</label>
    <input type="text" name="acc_term" class="form-control" value="{{ $data->getSData('acc_term') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Thanks Tip</label>
    <input type="text" name="thanks_tip" class="form-control" value="{{ $data->getSData('thanks_tip') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Tip Desc</label>
    <input type="text" name="tip_desc" class="form-control" value="{{ $data->getSData('tip_desc') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">eCash Desc</label>
    <input type="text" name="ecash_not" class="form-control" value="{{ $data->getSData('ecash_not') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Rider Tip</label>
    <input type="text" name="rider_tip" class="form-control" value="{{ $data->getSData('rider_tip') }}">
    </div>
    
    <div class="form-group col-md-4">
    <label for="inputEmail6">Most Order</label>
    <input type="text" name="most_order" class="form-control" value="{{ $data->getSData('most_order') }}">
    </div>
    
    </div>
    
    </div>
    
    @can('texts.edit')
    <div class="d-flex justify-content-end border-top pt-2 mt-1" style="position:sticky;bottom:0;background:var(--co-surface);padding-bottom:16px;z-index:2">
    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save changes</button>
    </div>
    @endcan
    </div>
    </div>