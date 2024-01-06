<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TempCustomer;
use App\Models\OrderChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Order_file_upload;
use App\Models\CustomerEmParameter;
use App\Models\CustomerVeParameter;
use App\Models\TempCustomerEmParameter;
use App\Models\TempCustomerVeParameter;
use App\Models\TempOrder;
use App\Models\Chat;
use App\Models\ChatMessage;
use DateTimeZone;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmRegisterMail;
use App\Mail\DeclineRegisterMail;
use App\Mail\ChangeProfileConfirmMail;
use App\Mail\ChangeProfileDeclineMail;
use App\Mail\ChangeEmParameterConfirmMail;
use App\Mail\ChangeVeParameterConfirmMail;
use App\Mail\ChangeEmParameterDeclineMail;
use App\Mail\ChangeVeParameterDeclineMail;
use jeremykenedy\Slack\Client as SlackClient;




class AdminController extends Controller
{
    public function adminloginpage()
    {
        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'password' => 'required',

        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            if (Auth::user()->user_type == "admin") {
                return redirect('/');
                // return redirect()->route('admin-vieworders', app()->getLocale())->with('success', 'You have successfully logged in');
            } else {
                Auth::logout();
                return redirect(__('routes.admin-login'))->with('danger', 'Oops! You do not have the required access permission');
            }
        }
        return redirect(__('routes.admin-login'))->with('danger', 'Oppes! You have entered invalid credentials');
    }

    public function goAdminLogin()
    {
        return redirect()->route('admin-login', ['locale' => app()->getLocale()]);
    }

    public function adminLogout()
    {
        Session::flush();
        Auth::logout();

        return Redirect(__('routes.admin-login'));
    }
    public function adminChangeAvatar()
    {
        return view('admin.change-avatar');
    }
    public function adminChangEAvatarHandle(Request $request)
    {
        $avatar_file = $request->file('change_avatar');
        $upload_dir = 'public/';
        $folder = 'admin-avatar';
        $avatar_filename = $avatar_file->getClientOriginalName();
        if (strlen($avatar_file->getClientOriginalName()) != 1) {
            Storage::makeDirectory($upload_dir);
            if ($avatar_file->storeAs($folder, $avatar_filename, 'public')) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $avatar_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/storage' . '/' . $folder . '/' . $avatar_filename;
                $fullPath = '/public' . '/' . $folder . '/' . $avatar_filename;
                $file_path = Storage::path($fullPath);
                echo $file_path;
                chmod($file_path, 0755);
                $publicPath = public_path();
                $publicStoragePath = $publicPath . '/storage';
                chmod($publicStoragePath, 0755);
                $admin = User::where('user_type', 'admin')->first();
                $admin->image = $avatar_url;
                $admin->save();
            }
        }
        return redirect('/');
    }

    public function viewOrders(Request $request)
    {

        $authuser = auth()->user()->id;
        if ($request->status_filter != '') {
            if ($request->status_filter == 'pending') {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->where('status', 'pending')->get();
            }
            if ($request->status_filter == 'delivered') {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->where('status', 'delivered')->get();
            }
            return Datatables::of($data)->addIndexColumn()
                ->editColumn('catgory', function ($row) {
                    $category = $row->category->category_name;
                    return $category;
                })
                ->editColumn('user', function ($row) {
                    $user = $row->user->name;
                    return $user;
                })
                ->editColumn('date', function ($row) {
                    $date = $row->created_at->format('Y-m-d');
                    return $date;
                })
                ->editColumn('selection', function ($row) {
                    $date = __($row->selection);
                    return $date;
                })
                ->addColumn('action', function ($row) {

                    $btn = '<div class="d-flex" style="gap:20px;">
                                    <div><a class="btn btn-secondary btn-sm" href=' . __("routes.admin-orderdetails") . $row->id . '>Order detail</a></div>
                                </div>';
                    return $btn;
                })
                ->rawColumns(['catgory', 'date', 'user', 'action', 'selection'])
                ->make(true);
        } else {
            if ($request->ajax()) {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->get();
                return Datatables::of($data)->addIndexColumn()
                    ->editColumn('catgory', function ($row) {
                        $category = $row->category->category_name;
                        return $category;
                    })
                    ->editColumn('user', function ($row) {
                        $user = $row->user->name;
                        return $user;
                    })
                    ->editColumn('date', function ($row) {
                        $date = $row->created_at->format('Y-m-d');
                        return $date;
                    })
                    ->editColumn('selection', function ($row) {
                        $date = __($row->selection);
                        return $date;
                    })
                    ->addColumn('action', function ($row) {

                        $btn = '<div class="d-flex" style="gap:20px;">
                                    <div><a class="btn btn-secondary btn-sm" href=' . __("routes.admin-orderdetails") . $row->id . '>Order detail</a></div>
                                </div>';
                        return $btn;
                    })
                    ->rawColumns(['catgory', 'date', 'user', 'action', 'selection'])
                    ->make(true);
            }
        }
        return view('admin.orders.vieworders');
    }

    public function changePassword()
    {
        return view('admin.changepassword');
    }

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'oldpassword' => 'required',
            'newpassword' => 'required',
            'password_confirmation' => 'required|same:newpassword'
        ]);

        $hashedPassword = Auth::user()->password;
        if (Hash::check($request->oldpassword, $hashedPassword)) {

            $users = User::find(Auth::user()->id);
            $users->password = bcrypt($request->newpassword);
            $users->save();
            session()->flash('message', 'Password updated successfully');
            return redirect(__('/'));
        } else {
            session()->flash('message', 'Old password does not matched');
            return redirect(__('routes.admin-changepassword'));
        }
    }

    public function adminProfile()
    {
        $authuser = auth()->user()->id;
        $user = User::where('id', $authuser)->first();
        return view('admin.profile.profile', compact('user'));
    }
    public function profileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('sidebar', true);
        }
        $user = auth()->user();
        $address = $request->address;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '-' . $file->getClientOriginalName();
            $destination = $user->id . '-profile' . $filename;
            $path = Storage::disk('s3')->put($destination, file_get_contents($file));
            $imageUrl = Storage::disk('s3')->url($destination);
            $user->image = $imageUrl;
        }
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->contact_no = $request->input('number');
        $user->address = $address;
        $user->save();
        return redirect()->back()->with('success', 'Profile updated successfully!')->with('sidebar', true);
    }
    public function orderDetails($locale, $id)
    {
        $order = Order::with('Order_address', 'Orderfile_uploads', 'Orderfile_formats')->where('id', $id)->first();
        return view('common.orderdetails', compact('order'));
    }


    public function AdminviewOrders(Request $request)
    {
        $authuser = auth()->user()->id;
        if ($request->status_filter != '') {
            if ($request->status_filter == 'pending') {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->where('status', 'pending')->get();
            }
            if ($request->status_filter == 'delivered') {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->where('status', 'delivered')->get();
            }
            return Datatables::of($data)->addIndexColumn()
                ->editColumn('catgory', function ($row) {
                    $category = $row->category->category_name;
                    return $category;
                })
                ->editColumn('user', function ($row) {
                    $user = $row->user->name;
                    return $user;
                })
                ->editColumn('date', function ($row) {
                    $date = $row->created_at->format('Y-m-d');
                    return $date;
                })
                ->editColumn('selection', function ($row) {
                    $date = __($row->selection);
                    return $date;
                })
                ->addColumn('action', function ($row) {

                    $btn = '<div class="d-flex" style="gap:20px;">
                                    <div><a class="btn btn-secondary btn-sm" href=' . __("routes.admin-orderdetails") . $row->id . '>Order detail</a></div>
                                </div>';
                    return $btn;
                })
                ->rawColumns(['catgory', 'date', 'user', 'action', 'selection'])
                ->make(true);
        } else {
            if ($request->ajax()) {
                $data = Order::with('Order_address', 'category', 'user')->orderBy('id', 'desc')->get();
                return Datatables::of($data)->addIndexColumn()
                    ->editColumn('catgory', function ($row) {
                        $category = $row->category->category_name;
                        return $category;
                    })
                    ->editColumn('user', function ($row) {
                        $user = $row->user->name;
                        return $user;
                    })
                    ->editColumn('date', function ($row) {
                        $date = $row->created_at->format('Y-m-d');
                        return $date;
                    })
                    ->editColumn('selection', function ($row) {
                        $date = __($row->selection);
                        return $date;
                    })
                    ->addColumn('action', function ($row) {

                        $btn = '<div class="d-flex" style="gap:20px;">
                                    <div><a class="btn btn-secondary btn-sm" href=' . __("routes.admin-orderdetails") . $row->id . '>Order detail</a></div>
                                </div>';
                        return $btn;
                    })
                    ->rawColumns(['catgory', 'date', 'user', 'action', 'selection'])
                    ->make(true);
            }
        }
        return response()->json([
            'messgae' => 'done'
        ]);
    }

    public function CustomerList(Request $request)
    {
        if ($request->ajax()) {
            $data = User::orderBy('id', 'desc')->where('user_type', 'customer')->get();
            $temp_data = TempCustomer::orderBy('id', 'desc')->get();
            // $order_changes = OrderChange::orderBy('id', 'desc')->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('edit', function ($row) {
                    $edit = '
                        <div class="d-flex" style="gap:20px;">
                            <div style="display: flex; margin:auto;">
                                <button onclick="editCustomerProfile(' . $row->id . ')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button>
                            </div>
                        </div>';
                    return $edit;
                })
                ->addColumn('request', function ($row) use ($temp_data) {
                    $req = '';
                    foreach ($temp_data as $temp) {
                        if ($temp->customer_id == $row->id) {
                            $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="HandleProfileRequest(' . $row->id . ')" style="border:none; background-color:inherit;"><i class="fa-solid fa-exclamation blink" style="color:#ff0000; transform:scale(2,1);"></i></button>
                                    </div>
                                </div>
                            ';
                        }
                    }
                    return $req;
                })
                ->addColumn('delete', function ($row) {
                    $delete = '<div class="d-flex" style="gap:20px;">
                            <div style="display: flex; margin:auto;">
                                <button onclick="DeleteCustomer(' . $row->id . ')" style="border:none; background-color:inherit; display:block; margin:auto;"><img src="' . asset('asset/images/trash-solid.svg') . '" alt="order-delete-icon" class="icon_size"></button>
                            </div>
                        </div>';
                    return $delete;
                })
                ->rawColumns(['edit', 'request', 'delete'])
                ->make(true);
        }
    }
    public function deleteCustomer(Request $request)
    {
        $customer = User::findOrfail($request->post('id'));
        User::where('user_type', 'employer')->where('org_id', $customer->id)->delete();
        $orders = Order::where('user_id', $customer->id)->get();
        foreach ($orders as $order) {
            TempOrder::where('order_id', $order->id)->delete();
            Order_file_upload::where('order_id', $order->id)->delete();
            $order->delete();
        }
        OrderChange::where('customer_id', $customer->id)->delete();
        TempCustomer::where('customer_id', $customer->id)->delete();
        CustomerEmParameter::where('customer_id', $customer->id)->delete();
        CustomerVeParameter::where('customer_id', $customer->id)->delete();
        TempCustomerEmParameter::where('customer_id', $customer->id)->delete();
        TempCustomerVeParameter::where('customer_id', $customer->id)->delete();
        $customer->delete();
    }
    public function getDifferences($locale, $id)
    {
        $user = User::findOrFail($id);
        $tempCustomer = TempCustomer::where('customer_id', $id)->orderBy('id', 'desc')->first();
        // $order_changes = OrderChange::where('customer_id', $id)->orderBy('id', 'desc')->get();

        $responseText = '';
        $count = 0;

        // foreach ($order_changes as $order_change) {
        //     $responseText .= '"' . $order_change["customer_name"] . '" hat eine Nachricht zu Bestellnummer ' . $order_change['order_number'] . ' gesendet: „' . $order_change['message'] . '“<br />';
        // }

        if ($tempCustomer) {
            if ($user['name'] != $tempCustomer['name']) {
                $responseText .= 'Der Kundin hat eine Änderung des "name" von "' . $user['name'] . '" in "' . $tempCustomer['name'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['first_name'] != $tempCustomer['first_name']) {
                $responseText .= 'Der Kundin hat eine Änderung des "first_name" von "' . $user['first_name'] . '" in "' . $tempCustomer['first_name'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['email'] != $tempCustomer['email']) {
                $responseText .= 'Der Kundin hat eine Änderung des "email" von "' . $user['email'] . '" in "' . $tempCustomer['email'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['company'] != $tempCustomer['company']) {
                $responseText .= 'Der Kundin hat eine Änderung des "company" von "' . $user['company'] . '" in "' . $tempCustomer['company'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['company_addition'] != $tempCustomer['company_addition']) {
                $responseText .= 'Der Kundin hat eine Änderung des "company_addition" von "' . $user['company_addition'] . '" in "' . $tempCustomer['company_addition'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['street_number'] != $tempCustomer['street_number']) {
                $responseText .= 'Der Kundin hat eine Änderung des "street_number" von "' . $user['street_number'] . '" in "' . $tempCustomer['street_number'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['postal_code'] != $tempCustomer['postal_code']) {
                $responseText .= 'Der Kundin hat eine Änderung des "postal_code" von "' . $user['postal_code'] . '" in "' . $tempCustomer['postal_code'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['location'] != $tempCustomer['location']) {
                $responseText .= 'Der Kundin hat eine Änderung des "location" von "' . $user['location'] . '" in "' . $tempCustomer['location'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['country'] != $tempCustomer['country']) {
                $responseText .= 'Der Kundin hat eine Änderung des "country" von "' . $user['country'] . '" in "' . $tempCustomer['country'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['website'] != $tempCustomer['website']) {
                $responseText .= 'Der Kundin hat eine Änderung des "websitewebsite" von "' . $user['name'] . '" in "' . $tempCustomer['name'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['phone'] != $tempCustomer['phone']) {
                $responseText .= 'Der Kundin hat eine Änderung des "phone" von "' . $user['phone'] . '" in "' . $tempCustomer['phone'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['mobile'] != $tempCustomer['mobile']) {
                $responseText .= 'Der Kundin hat eine Änderung des "mobile" von "' . $user['mobile'] . '" in "' . $tempCustomer['mobile'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['tax_number'] != $tempCustomer['tax_number']) {
                $responseText .= 'Der Kundin hat eine Änderung des "tax_number" von "' . $user['tax_number'] . '" in "' . $tempCustomer['tax_number'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['vat_number'] != $tempCustomer['vat_number']) {
                $responseText .= 'Der Kundin hat eine Änderung des "vat_number" von "' . $user['vat_number'] . '" in "' . $tempCustomer['vat_number'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['register_number'] != $tempCustomer['register_number']) {
                $responseText .= 'Der Kundin hat eine Änderung des "register_number" von "' . $user['register_number'] . '" in "' . $tempCustomer['register_number'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['kd_group'] != $tempCustomer['kd_group']) {
                $responseText .= 'Der Kundin hat eine Änderung des "kd_group" von "' . $user['kd_group'] . '" in "' . $tempCustomer['kd_group'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['kd_category'] != $tempCustomer['kd_category']) {
                $responseText .= 'Der Kundin hat eine Änderung des "kd_category" von "' . $user['kd_category'] . '" in "' . $tempCustomer['kd_category'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['payment_method'] != $tempCustomer['payment_method']) {
                $responseText .= 'Der Kundin hat eine Änderung des "payment_method" von "' . $user['payment_method'] . '" in "' . $tempCustomer['payment_method'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['bank_name'] != $tempCustomer['bank_name']) {
                $responseText .= 'Der Kundin hat eine Änderung des "bank_name" von "' . $user['bank_name'] . '" in "' . $tempCustomer['bank_name'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['IBAN'] != $tempCustomer['IBAN']) {
                $responseText .= 'Der Kundin hat eine Änderung des "IBAN" von "' . $user['IBAN'] . '" in "' . $tempCustomer['IBAN'] . '" angefordert.<br />';
                $count++;
            }
            if ($user['BIC'] != $tempCustomer['BIC']) {
                $responseText .= 'Der Kundin hat eine Änderung des "BIC" von "' . $user['BIC'] . '" in "' . $tempCustomer['BIC'] . '" angefordert.<br />';
                $count++;
            }
        }
        $responseText = substr($responseText, 0, strlen($responseText) - 2);

        echo $responseText;
    }

    public function acceptChangeRequest(Request $request)
    {
        $user = User::findOrFail($request->post('id'));
        $tempCustomer = TempCustomer::where('customer_id', $request->post('id'))->orderBy('id', 'desc')->first();
        $user->name = $request->post('name');
        $user->first_name = $request->post('first_name');
        $user->email = $request->post('email');
        $user->company = $request->post('company');
        $user->company_addition = $request->post('company_addition');
        $user->street_number = $request->post('street_number');
        $user->postal_code = $request->post('postal_code');
        $user->location = $request->post('location');
        $user->country = $request->post('country');
        $user->website = $request->post('website');
        $user->phone = $request->post('phone');
        $user->mobile = $request->post('mobile');
        $user->tax_number = $request->post('tax_number');
        $user->vat_number = $request->post('vat_number');
        $user->register_number = $request->post('register_number');
        $user->kd_group = $request->post('kd_group');
        $user->kd_category = $request->post('kd_category');
        $user->payment_method = $request->post('payment_method');
        $user->bank_name = $request->post('bank_name');
        $user->IBAN = $request->post('IBAN');
        $user->BIC = $request->post('BIC');
        $user->save();
        $tempCustomer->delete();
        return 'successfully changed!';
    }
    public function AcceptProfileChangeMail(Request $request)
    {
        $customer = User::findOrfail($request->input('customer_id'));
        $recipient_customer = $customer->email;
        try {
            Mail::to($recipient_customer)->send(new ChangeProfileConfirmMail($customer));
            Mail::to('christoperw818@gmail.com')->send(new ChangeProfileConfirmMail($customer));
            Mail::to('habedere@sinzers.de')->send(new ChangeProfileConfirmMail($customer));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function declineChangeRequest($locale, $id)
    {
        $user = TempCustomer::where('customer_id', $id)->orderBy('id', 'desc')->first();
        $user->delete();
    }
    public function DeclineProfileChangeMail(Request $request)
    {
        $customer = User::findOrfail($request->input('customer_id'));
        $recipient_customer = $customer->email;
        try {
            Mail::to($recipient_customer)->send(new ChangeProfileDeclineMail($customer));
            Mail::to('christoperw818@gmail.com')->send(new ChangeProfileDeclineMail($customer));
            Mail::to('habedere@sinzers.de')->send(new ChangeProfileDeclineMail($customer));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function GetCustomerProfile(Request $request)
    {
        $profile = User::findOrfail($request->get('id'));
        $temp = TempCustomer::where('customer_id', $request->get('id'))->first();
        return response()->json(['profile' => $profile, 'temp' => $temp]);
    }

    public function ChangeProfile(Request $request)
    {
        $id = $request->post('id');
        $customer_number = $request->post('customer_number');
        $name = $request->post('name');
        $first_name = $request->post('first_name');
        $email = $request->post('email');
        $company = $request->post('company');
        $company_addition = $request->post('company_addition');
        $street_number = $request->post('street_number');
        $postal_code = $request->post('postal_code');
        $location = $request->post('location');
        $country = $request->post('country');
        $website = $request->post('website');
        $phone = $request->post('phone');
        $mobile = $request->post('mobile');
        $tax_number = $request->post('tax_number');
        $vat_number = $request->post('vat_number');
        $register_number = $request->post('register_number');
        $kd_group = $request->post('kd_group');
        $kd_category = $request->post('kd_category');
        $payment_method = $request->post('payment_method');
        $bank_name = $request->post('bank_name');
        $IBAN = $request->post('IBAN');
        $BIC = $request->post('BIC');

        $data = User::where('id', $id)->first();
        $data->customer_number = $customer_number;
        $data->name = $name;
        $data->first_name = $first_name;
        $data->email = $email;
        $data->company = $company;
        $data->company_addition = $company_addition;
        $data->street_number = $street_number;
        $data->postal_code = $postal_code;
        $data->location = $location;
        $data->country = $country;
        $data->website = $website;
        $data->phone = $phone;
        $data->mobile = $mobile;
        $data->tax_number = $tax_number;
        $data->vat_number = $vat_number;
        $data->register_number = $register_number;
        $data->kd_group = $kd_group;
        $data->kd_category = $kd_category;
        $data->payment_method = $payment_method;
        $data->bank_name = $bank_name;
        $data->IBAN = $IBAN;
        $data->BIC = $BIC;
        $data->save();
    }
    public function AddCustomer(Request $request)
    {
        $customer_number = $request->post('customer_number');
        $name = $request->post('name');
        $first_name = $request->post('first_name');
        $company = $request->post('company');
        $company_addition = $request->post('company_addition');
        $street_number = $request->post('street_number');
        $postal_code = $request->post('postal_code');
        $location = $request->post('location');
        $country = $request->post('country');
        $website = $request->post('website');
        $phone = $request->post('phone');
        $mobile = $request->post('mobile');
        $tax_number = $request->post('tax_number');
        $vat_number = $request->post('vat_number');
        $register_number = $request->post('register_number');
        $kd_group = $request->post('kd_group');
        $kd_category = $request->post('kd_category');
        $payment_method = $request->post('payment_method');
        $bank_name = $request->post('bank_name');
        $IBAN = $request->post('IBAN');
        $BIC = $request->post('BIC');
        $email = $request->post('email');
        $password = $request->post('password');

        $add_customer = User::create([
            'customer_number' => $customer_number,
            'name' => $name,
            'first_name' => $first_name,
            'company' => $company,
            'company_addition' => $company_addition,
            'street_number' => $street_number,
            'postal_code' => $postal_code,
            'location' => $location,
            'country' => $country,
            'website' => $website,
            'phone' => $phone,
            'mobile' => $mobile,
            'tax_number' => $tax_number,
            'vat_number' => $vat_number,
            'register_number' => $register_number,
            'kd_group' => $kd_group,
            'kd_category' => $kd_category,
            'payment_method' => $payment_method,
            'bank_name' => $bank_name,
            'IBAN' => $IBAN,
            'BIC' => $BIC,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'customer',
            'image' => '',
        ]);
        $add_customer->save();
    }
    public function CustomerSearchTable(Request $request)
    {
        $customers = User::orderBy('id', 'desc')->where('user_type', 'customer')
            ->where(function ($query) use ($request) {
                $query->where('customer_number', 'LIKE', '%' . $request->search_filter . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search_filter . '%')
                    ->orWhere('company', 'LIKE', '%' . $request->search_filter . '%')
                    ->orWhere('postal_code', 'LIKE', '%' . $request->search_filter . '%');
            })->get();

        $html = '';
        $data = [];
        if (count($customers) > 0) {
            foreach ($customers as $item) {
                $html .= '<tr><td>' . $item->customer_number . '</td>' .
                    '<td>' . $item->company . '</td>' .
                    '<td>' . $item->name . '</td>' .
                    '<td>' . $item->first_name . '</td>' .
                    '<td>' . $item->phone . '</td>' .
                    '<td>' . $item->email . '</td>' .
                    '<td>' . $item->street_number . '</td>' .
                    '<td>' . $item->postal_code . '</td>' .
                    '<td>' . $item->location . '</td>' .
                    '<td>' . $item->country . '</td>' .
                    '<td style="max-width:50px !important;"><input type="checkbox" name="selected_customer" value="' . $item->id . '"></td></tr>';
                $data['id'] = $item->id;
                $data['html'] = $html;
            }
        } else {
            $html .= '<tr><td colspan="11" class="text-center">Keine Daten</td></tr>';
            $data['html'] = $html;
        }
        echo json_encode($data);
    }
    public function CustomerSearchResult(Request $request)
    {
        $customer = User::findOrfail($request->get('id'));
        return response()->json($customer);
    }
    public function adminFileUpload(Request $request)
    {
        $type = $request->post('type');
        $deliver_time = $request->post('deliver_time');
        $project_name = $request->post('project_name');
        $size = $request->post('size');
        $width_height = $request->post('width_height');
        $products = $request->post('products');
        $special_instructions = $request->post('special_instructions');
        $customer_number = $request->post('customer_number');
        $searched_id = $request->post('searched_id');
        $last_order = Order::where('customer_number', $customer_number)->orderBy('order_number', 'desc')->first();

        $order = Order::where('type', $type)
            ->where('project_name', $project_name)
            ->where('size', $size)
            ->where('deliver_time', $deliver_time)
            ->where('width_height', $width_height)
            ->where('products', $products)
            ->where('special_instructions', $special_instructions)->first();

        if ($order == null) {
            $order = new Order();
            $order->customer_number = $customer_number;
            $order->order_number = $last_order == null ? '0001' : sprintf('%04s', $last_order->order_number + 1);
            $order->project_name = $project_name;
            $order->ordered_from = "Lion Werbe GmbH";
            $order->status = 'Offen';
            $order->type = $type;
            $order->size = $size;
            $order->deliver_time = $deliver_time;
            $order->width_height = $width_height;
            $order->products = $products;
            $order->special_instructions = $special_instructions;
            $order->category_id = 1;
            $order->user_id = $searched_id;
            $order->assigned_to = 4;
            $order->org_id = $searched_id;
            $order->save();
        }

        $files = $request->file("files");
        $uploadDir = 'public/';
        $filePath = $order->customer_number . '/' .
            $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/Originaldatei/';
        $path = $uploadDir . $filePath;
        foreach ($files as $key => $file) {
            // Check whether the current entity is an actual file or a folder (With a . for a name)
            if (strlen($file->getClientOriginalName()) != 1) {
                Storage::makeDirectory($uploadDir);
                $fileName = $file->getClientOriginalName();

                if ($file->storeAs($filePath, $fileName, 'public')) {
                    $order_file_upload = new Order_file_upload();
                    $order_file_upload->order_id = $order->id;
                    $order_file_upload->extension = $file->getClientOriginalExtension();
                    $order_file_upload->base_url = 'storage/' . $filePath . $fileName;
                    $order_file_upload->save();
                    $fullPath = '/public' . '/' . $filePath . $fileName;
                    $file_path = Storage::path($fullPath);
                    chmod($file_path, 0755);
                    $publicPath = public_path();
                    $publicStoragePath = $publicPath . '/storage';
                    chmod($publicStoragePath, 0755);
                    echo "The file " . $fileName . " has been uploaded";
                } else
                    echo "Error";
            }
        }
        return "OK!";
    }
    public function ConfirmProfile(Request $request)
    {
        $id = $request->post('admin_confirm_profile_id');
        $customer_number = $request->post('customer_number');
        $user = User::findOrfail($id);
        $user->customer_number = $customer_number;
        $user->save();
    }
    public function ConfirmProfileMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $recipient_customer = $customer->email;
        try {
            Mail::to('habedere@sinzers.de')->send(new ConfirmRegisterMail($customer));
            Mail::to($recipient_customer)->send(new ConfirmRegisterMail($customer));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function DeclineProfile(Request $request)
    {
        $id = $request->post('admin_decline_profile_id');
        User::findOrfail($id)->delete();
    }
    public function DeclineProfileMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $recipient_customer = $customer->email;
        try {
            Mail::to($recipient_customer)->send(new DeclineRegisterMail($customer));
            Mail::to('habedere@sinzers.de')->send(new DeclineRegisterMail($customer));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }


    }
    public function EmParameterTable(Request $request)
    {
        if ($request->ajax()) {
            $data = User::orderBy('id', 'desc')->where('user_type', 'customer')->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('parameter', function ($row) {
                    $parameter = '
                        <div class="d-flex" style="gap:20px;">
                            <div style="display: flex; margin:auto;">
                                <button onclick="openEmParameter(' . $row->id . ')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button>
                            </div>
                        </div>';

                    return $parameter;
                })
                ->rawColumns(['parameter'])
                ->make(true);
        }
    }
    public function VeParameterTable(Request $request)
    {
        if ($request->ajax()) {
            $data = User::orderBy('id', 'desc')->where('user_type', 'customer')->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('parameter', function ($row) {
                    $parameter = '
                        <div class="d-flex" style="gap:20px;">
                            <div style="display: flex; margin:auto;">
                                <button onclick="openVeParameter(' . $row->id . ')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button>
                            </div>
                        </div>';
                    return $parameter;
                })
                ->rawColumns(['parameter'])
                ->make(true);
        }
    }
    public function EmParameter(Request $request)
    {
        $parameter = CustomerEmParameter::where('customer_id', $request->get('id'))->first();
        $temp_parameter = TempCustomerEmParameter::where('customer_id', $request->get('id'))->first();
        return response()->json([$parameter, $temp_parameter]);
    }
    public function VeParameter(Request $request)
    {
        $parameter = CustomerVeParameter::where('customer_id', $request->get('id'))->first();
        $temp_parameter = TempCustomerVeParameter::where('customer_id', $request->get('id'))->first();
        return response()->json([$parameter, $temp_parameter]);
    }
    public function EmParameterChange(Request $request)
    {
        $parameter = CustomerEmParameter::where('customer_id', $request->post('customer_id'))->first();
        if ($parameter) {
            $parameter->parameter1 = $request->post('parameter1');
            $parameter->parameter2 = $request->post('parameter2');
            $parameter->parameter3 = $request->post('parameter3');
            $parameter->parameter4 = $request->post('parameter4');
            $parameter->parameter5 = $request->post('parameter5');
            $parameter->parameter6 = $request->post('parameter6');
            $parameter->parameter7 = $request->post('parameter7');
            $parameter->save();
        } else {
            $parameter = new CustomerEmParameter();
            $parameter->customer_id = $request->post('customer_id');
            $parameter->parameter1 = $request->post('parameter1');
            $parameter->parameter2 = $request->post('parameter2');
            $parameter->parameter3 = $request->post('parameter3');
            $parameter->parameter4 = $request->post('parameter4');
            $parameter->parameter5 = $request->post('parameter5');
            $parameter->parameter6 = $request->post('parameter6');
            $parameter->parameter7 = $request->post('parameter7');
            $parameter->save();
        }

    }
    public function VeParameterChange(Request $request)
    {
        $parameter = CustomerVeParameter::where('customer_id', $request->post('customer_id'))->first();
        if ($parameter) {
            $parameter->parameter8 = $request->post('parameter8');
            $parameter->parameter9 = $request->post('parameter9');
            $parameter->save();
        } else {
            $parameter = new CustomerVeParameter();
            $parameter->customer_id = $request->post('customer_id');
            $parameter->parameter8 = $request->post('parameter8');
            $parameter->parameter9 = $request->post('parameter9');
            $parameter->save();
        }

    }
    public function EmParameterConfirm(Request $request)
    {
        $parameter = CustomerEmParameter::where('customer_id', $request->post('customer_id'))->first();
        $test_parameter = TempCustomerEmParameter::where('customer_id', $request->post('customer_id'))->first();
        $parameter->parameter1 = $request->post('parameter1');
        $parameter->parameter2 = $request->post('parameter2');
        $parameter->parameter3 = $request->post('parameter3');
        $parameter->parameter4 = $request->post('parameter4');
        $parameter->parameter5 = $request->post('parameter5');
        $parameter->parameter6 = $request->post('parameter6');
        $parameter->parameter7 = $request->post('parameter7');
        $parameter->save();
        $test_parameter->delete();
    }
    public function VeParameterConfirm(Request $request)
    {
        $parameter = CustomerVeParameter::where('customer_id', $request->post('customer_id'))->first();
        $test_parameter = TempCustomerVeParameter::where('customer_id', $request->post('customer_id'))->first();
        $parameter->parameter8 = $request->post('parameter8');
        $parameter->parameter9 = $request->post('parameter9');
        $parameter->save();
        $test_parameter->delete();
    }
    public function EmParameterDecline(Request $request)
    {
        $test_parameter = TempCustomerEmParameter::where('customer_id', $request->post('customer_id'))->first();
        $parameter = CustomerEmParameter::where('customer_id', $request->post('customer_id'))->first();
        $test_parameter->delete();
    }
    public function VeParameterDecline(Request $request)
    {
        $test_parameter = TempCustomerVeParameter::where('customer_id', $request->post('customer_id'))->first();
        $parameter = CustomerVeParameter::where('customer_id', $request->post('customer_id'))->first();
        $test_parameter->delete();
    }
    public function EmParameterConfirmMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $parameter = CustomerEmParameter::where('customer_id', $request->get('customer_id'))->first();
        $recipient_customer = $customer->email;

        try {
            // Mail::to($recipient_customer)->send(new ChangeEmParameterConfirmMail($customer, $parameter));
            Mail::to('habedere@sinzers.de')->send(new ChangeEmParameterConfirmMail($customer, $parameter));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function VeParameterConfirmMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $parameter = CustomerVeParameter::where('customer_id', $request->get('customer_id'))->first();
        $recipient_customer = $customer->email;

        try {
            Mail::to($recipient_customer)->send(new ChangeVeParameterConfirmMail($customer, $parameter));
            Mail::to('habedere@sinzers.de')->send(new ChangeVeParameterConfirmMail($customer, $parameter));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function EmParameterDeclineMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $parameter = CustomerEmParameter::where('customer_id', $request->get('customer_id'))->first();
        $recipient_customer = $customer->email;

        try {
            // Mail::to($recipient_customer)->send(new ChangeEmParameterDeclineMail($customer, $parameter));
            Mail::to('habedere@sinzers.de')->send(new ChangeEmParameterDeclineMail($customer, $parameter));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function VeParameterDeclineMail(Request $request)
    {
        $customer = User::findOrfail($request->get('customer_id'));
        $parameter = CustomerVeParameter::where('customer_id', $request->get('customer_id'))->first();
        $recipient_customer = $customer->email;

        try {
            // Mail::to($recipient_customer)->send(new ChangeVeParameterDeclineMail($customer, $parameter));
            Mail::to('habedere@sinzers.de')->send(new ChangeVeParameterDeclineMail($customer, $parameter));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function AdminGreenTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('status', 'Offen')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="AdminOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
                    return $btn;
                })
                ->addColumn('deliver_time', function ($row) {
                    $deliver_time = '';
                    if ($row->deliver_time == "STANDARD") {
                        $deliver_time = "STANDARD";
                    } else if ($row->deliver_time == "EXPRESS") {
                        $deliver_time = '<div style="color:red;" class="blink">EXPRESS</div>';
                    }
                    return $deliver_time;
                })
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function AdminYellowTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('status', 'In Bearbeitung')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {
                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="AdminOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
                    return $btn;
                })
                ->addColumn('deliver_time', function ($row) {
                    $deliver_time = '';
                    if ($row->deliver_time == "STANDARD") {
                        $deliver_time = "STANDARD";
                    } else if ($row->deliver_time == "EXPRESS") {
                        $deliver_time = '<div style="color:red;" class="blink">EXPRESS</div>';
                    }
                    return $deliver_time;
                })
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function AdminRedTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('status', 'Ausgeliefert')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="AdminOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
                    return $btn;
                })
                ->addColumn('deliver_time', function ($row) {
                    $deliver_time = '';
                    if ($row->deliver_time == "STANDARD") {
                        $deliver_time = "STANDARD";
                    } else if ($row->deliver_time == "EXPRESS") {
                        $deliver_time = "EXPRESS ";
                    }
                    return $deliver_time;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if ($row->status == "Ausgeliefert" || $row->status == "Änderung") {
                        $btn = '<button style="border:none; background:inherit; display:block; margin:auto;" onclick="AdminOpenOrderChangeModal(' . $row->id . ')"><img src="' . asset('asset/images/ÄndernIcon.svg') . '" class="icon_size"></button>';
                    }
                    return $btn;
                })
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time', 'action'])
                ->make(true);
        }
    }
    public function AdminBlueTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('status', 'Änderung')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="AdminOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
                    return $btn;
                })
                ->addColumn('deliver_time', function ($row) {
                    $deliver_time = '';
                    if ($row->deliver_time == "STANDARD") {
                        $deliver_time = "STANDARD";
                    } else if ($row->deliver_time == "EXPRESS") {
                        $deliver_time = "EXPRESS ";
                    }
                    return $deliver_time;
                })
                ->addColumn('request', function ($row) {
                    $req = '';

                    if ($row->status == 'Änderung') {
                        $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="AdminChange(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
                                    </div>
                                </div>
                            ';
                    }
                    return $req;
                })
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time', 'request'])
                ->make(true);
        }
    }
    public function AdminAllTable(Request $request)
    {

        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')
                ->where(function ($query) use ($request) {
                    $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                        ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                        ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                        ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                })->get();
            $order_changes = OrderChange::orderBy('id', 'desc')->get();
            if ($request->start_date_filter == '') {
                if ($request->end_date_filter == '') {
                    $data = Order::orderBy('id', 'desc')
                        ->where(function ($query) use ($request) {
                            $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                                ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                        })->get();
                } else {
                    $data = Order::orderBy('id', 'desc')
                        ->where(function ($query) use ($request) {
                            $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                                ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                        })
                        ->where('created_at', '<=', date('Y-m-d', strtotime('+1 day', strtotime($request->end_date_filter))))->get();
                }
            } else {
                if ($request->end_date_filter == '') {
                    $data = Order::orderBy('id', 'desc')
                        ->where(function ($query) use ($request) {
                            $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                                ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                        })
                        ->where('created_at', '>=', date('Y-m-d', strtotime($request->start_date_filter)))->get();
                } else {
                    $data = Order::orderBy('id', 'desc')
                        ->where(function ($query) use ($request) {
                            $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                                ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                        })
                        ->whereBetween('created_at', [date('Y-m-d', strtotime($request->start_date_filter)), date('Y-m-d', strtotime('+1 day', strtotime($request->end_date_filter)))])->get();
                }
            }

            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if ($row->status == "Ausgeliefert" || $row->status == "Änderung") {
                        $btn = '<button style="border:none; background:inherit; display:block; margin:auto;" onclick="AdminOpenOrderChangeModal(' . $row->id . ')"><img src="' . asset('asset/images/ÄndernIcon.svg') . '" class="icon_size"></button>';
                    }
                    return $btn;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<button style="border:none; background:inherit; display:block; margin:auto;" onclick="AdminOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button>';
                    return $btn;
                })
                ->addColumn('deliver_time', function ($row) {
                    $deliver_time = '';
                    if ($row->deliver_time == "STANDARD") {
                        $deliver_time = "STANDARD";
                    } else if ($row->deliver_time == "EXPRESS") {
                        $deliver_time = "EXPRESS ";
                    }
                    return $deliver_time;
                })
                ->addColumn('delete', function ($row) {
                    $delete = '<button style="border:none; background:inherit; display:block; margin:auto;" onclick="deleteOrder(' . $row->id . ')"><img src="' . asset('asset/images/trash-solid.svg') . '" alt="order-delete-icon" class="icon_size"></button>';
                    return $delete;
                })
                ->addColumn('request', function ($row) {
                    $req = '';

                    if ($row->status == 'Änderung') {
                        $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="AdminChange(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
                                    </div>
                                </div>
                            ';
                    }
                    return $req;
                })
                ->rawColumns(['order', 'action', 'date', 'detail', 'status', 'type', 'deliver_time', 'delete', 'request'])
                ->make(true);
        }

    }
    public function DeleteOrder(Request $request)
    {
        $delete_id = $request->post('delete_id');
        $order = Order::findOrfail($delete_id);
        $order->delete();
        TempOrder::where('order_id', $delete_id)->delete();
        Order_file_upload::where('order_id', $delete_id)->delete();
        OrderChange::where('order_id', $delete_id)->delete();

    }
    public function JobFileUpload(Request $request)
    {
        $order_id = $request->post('admin_detail_id');
        $order = Order::findOrfail($order_id);


        $order->status = 'In Bearbeitung';
        $order->save();


        $files = $request->file("files");
        $uploadDir = 'public/';
        if ($order->type == 'Embroidery') {
            $filePath = $order->customer_number . '/' .
                $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/Stickprogramm/';
        } else if ($order->type == 'Vector') {
            $filePath = $order->customer_number . '/' .
                $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/Vektordatei/';
        }
        $path = $uploadDir . $filePath;
        foreach ($files as $key => $file) {
            // Check whether the current entity is an actual file or a folder (With a . for a name)
            if (strlen($file->getClientOriginalName()) != 1) {
                Storage::makeDirectory($uploadDir);
                $fileName = $file->getClientOriginalName();

                if ($file->storeAs($filePath, $fileName, 'public')) {
                    $order_file_upload = new Order_file_upload();
                    $order_file_upload->order_id = $order->id;
                    $order_file_upload->extension = $file->getClientOriginalExtension();
                    $order_file_upload->base_url = 'storage/' . $filePath . $fileName;
                    $order_file_upload->save();
                    $fullPath = '/public' . '/' . $filePath . $fileName;
                    $file_path = Storage::path($fullPath);
                    chmod($file_path, 0755);
                    $publicPath = public_path();
                    $publicStoragePath = $publicPath . '/storage';
                    chmod($publicStoragePath, 0755);
                    echo "The file " . $fileName . " has been uploaded";
                } else
                    echo "Error";
            }
        }
        return "OK!";
    }
    public function ChangeFileUpload(Request $request)
    {
        $freelancer_request_id = $request->post('admin_change_id');
        $order = Order::findOrfail($freelancer_request_id);
        $customer = User::findOrfail($order->user_id);
        $time = $request->post('admin_change_time');

        OrderChange::where('time', $time)->delete();
        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order->id;
        $order_change->change_number = $change_number + 1;
        $order_change->time = $time;
        if ($order->type == 'Embroidery') {
            $order_change->changed_from = "freelancer_em";
        } else {
            $order_change->changed_from = "freelancer_ve";
        }
        $order_change->save();


        $files = $request->file("files");
        $uploadDir = 'public/';
        $filePath = $order->customer_number . '/' .
            $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/';
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer' . '%')->count();
        $folderName = 'Stickprogramm Änderung' . ($folderCount) . '/';
        $path = $uploadDir . $filePath . $folderName;
        foreach ($files as $key => $file) {
            // Check whether the current entity is an actual file or a folder (With a . for a name)
            if (strlen($file->getClientOriginalName()) != 1) {
                Storage::makeDirectory($uploadDir);
                $fileName = $file->getClientOriginalName();

                if ($file->storeAs($filePath . $folderName, $fileName, 'public')) {
                    $order_file_upload = new Order_file_upload();
                    $order_file_upload->order_id = $order->id;
                    $order_file_upload->extension = $file->getClientOriginalExtension();
                    $order_file_upload->base_url = 'storage/' . $filePath . $folderName . $fileName;
                    $order_file_upload->save();
                    $fullPath = '/public' . '/' . $filePath . $folderName . $fileName;
                    $file_path = Storage::path($fullPath);
                    chmod($file_path, 0755);
                    $publicPath = public_path();
                    $publicStoragePath = $publicPath . '/storage';
                    chmod($publicStoragePath, 0755);
                    echo "The file " . $fileName . " has been uploaded";
                } else
                    echo "Error";
            }
        }
        return "OK!";
    }
    public function EndChange(Request $request)
    {
        $order = Order::findOrfail($request->get('end_change_id'));
        $order_change_count = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer' . '%')->count();
        if ($order_change_count > 0) {
            $order->status = 'Ausgeliefert';
            $order->save();
        } else {
            return response()->json(['message' => 'Error'], 500);
        }
    }
    public function RequestText(Request $request)
    {
        $order_id = $request->post('admin_request_id');
        $order_change_message = $request->post('admin_order_request_text');
        $time = $request->post('admin_request_time');

        $order = Order::findOrfail($order_id);
        $customer = User::findOrfail($order->user_id);

        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order->id;
        $order_change->message = $order_change_message;
        $order_change->change_number = $change_number + 1;
        $order_change->time = $time;
        if ($order->type == "Embroidery") {
            $order_change->changed_from = "customer_em";
        } else if ($order->type == "Vector") {
            $order_change->changed_from = "customer_ve";
        }
        $order_change->save();

        $order->status = 'Änderung';
        $order->save();
    }
    public function RequestFileUpload(Request $request)
    {
        $order_id = $request->post('admin_request_id');
        $order_change_message = $request->post('admin_order_request_text');
        $time = $request->post('admin_request_time');

        $order = Order::findOrfail($order_id);
        $customer = User::findOrfail($order->user_id);


        OrderChange::where('time', $time)->delete();
        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order->id;
        $order_change->message = $order_change_message;
        $order_change->change_number = $change_number + 1;
        $order_change->time = $time;
        if ($order->type == "Embroidery") {
            $order_change->changed_from = "customer_em";
        } else if ($order->type == "Vector") {
            $order_change->changed_from = "customer_ve";
        }
        $order_change->save();

        $order->status = 'Änderung';
        $order->save();
        $files = $request->file("files");
        $uploadDir = 'public/';
        $filePath = $order->customer_number . '/' .
            $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/';
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->count();
        $folderName = 'Änderungsdateien Kunde' . ($folderCount) . '/';
        $path = $uploadDir . $filePath . $folderName;
        foreach ($files as $key => $file) {
            // Check whether the current entity is an actual file or a folder (With a . for a name)
            if (strlen($file->getClientOriginalName()) != 1) {
                Storage::makeDirectory($uploadDir);
                $fileName = $file->getClientOriginalName();

                if ($file->storeAs($filePath . $folderName, $fileName, 'public')) {
                    $order_file_upload = new Order_file_upload();
                    $order_file_upload->order_id = $order->id;
                    $order_file_upload->extension = $file->getClientOriginalExtension();
                    $order_file_upload->base_url = 'storage/' . $filePath . $folderName . $fileName;
                    $order_file_upload->save();
                    $fullPath = '/public' . '/' . $filePath . $folderName . $fileName;
                    $file_path = Storage::path($fullPath);
                    chmod($file_path, 0755);
                    $publicPath = public_path();
                    $publicStoragePath = $publicPath . '/storage';
                    chmod($publicStoragePath, 0755);
                    echo "The file " . $fileName . " has been uploaded";
                } else
                    echo "Error";
            }
        }
    }
    public function AdminOrderDetail(Request $request)
    {
        $authuser = auth()->user();
        if ($request->ajax()) {
            $change_data = Order_file_upload::where('order_id', $request->id)->where('base_url', 'LIKE', '%' . $request->type . '%')->orderBy('order_id', 'desc')->get();
            if ($request->type == 'Stickprogramm') {
                $change_data = Order_file_upload::where('order_id', $request->id)->where('base_url', 'LIKE', '%' . $request->type . '%')->where('base_url', 'NOT LIKE', '%Stickprogramm Änderung%')->orderBy('order_id', 'desc')->get();
            }

            return DataTables::of($change_data)->addIndexColumn()
                ->editColumn('customer_number', function ($row) {
                    $customer_number = $row->order->customer_number;
                    return $customer_number;
                })
                ->editColumn('order_number', function ($row) {
                    $order_number = $row->order->order_number;
                    return $order_number;
                })

                ->addColumn('download', function ($row) {

                    $btn = '<a href="' . asset($row->base_url) . '" download><button type="button" style="background:none; border:none; padding:0;"><i class="fa-solid fa-download download_icon"></i></button></a>';
                    return $btn;
                })
                ->addColumn('delete', function ($row) use ($change_data) {
                    $btn = '';
                    foreach ($change_data as $change_item) {
                        $btn = '<button onClick = AdminDeleteFile(' . $row->id . ') style="border:none; background:inherit;"><i class="fa-solid fa-trash-can" style="color:#c4ae79;"></i></button>';
                    }

                    return $btn;
                })


                ->rawColumns(['customer_number', 'order_number', 'download', 'delete'])
                ->make(true);
        }
    }
    public function AdminChangeOrderDetail(Request $request)
    {
        $authuser = auth()->user();
        if ($request->ajax()) {
            $change_data = Order_file_upload::where('order_id', $request->id)->where('base_url', 'LIKE', '%' . $request->type . '%')->orderBy('order_id', 'desc')->get();
            if ($request->type == 'Stickprogramm') {
                $change_data = Order_file_upload::where('order_id', $request->id)->where('base_url', 'LIKE', '%' . $request->type . '%')->where('base_url', 'NOT LIKE', '%Stickprogramm Änderung%')->orderBy('order_id', 'desc')->get();
            }

            return DataTables::of($change_data)->addIndexColumn()
                ->editColumn('customer_number', function ($row) {
                    $customer_number = $row->order->customer_number;
                    return $customer_number;
                })
                ->editColumn('order_number', function ($row) {
                    $order_number = $row->order->order_number;
                    return $order_number;
                })

                ->addColumn('download', function ($row) {

                    $btn = '<a href="' . asset($row->base_url) . '" download><button type="button" style="background:none; border:none; padding:0;"><i class="fa-solid fa-download download_icon"></i></button></a>';
                    return $btn;
                })
                ->addColumn('delete', function ($row) {
                    $btn = '<button onClick = AdminChangeDeleteFile(' . $row->id . ') style="border:none; background:inherit;"><i class="fa-solid fa-trash-can" style="color:#c4ae79;"></i></button>';
                    return $btn;
                })
                ->rawColumns(['customer_number', 'order_number', 'download', 'delete'])
                ->make(true);
        }
    }
    public function DashboardGreenTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('status', 'Offen')->take(5)->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->addColumn('art', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })

                ->rawColumns(['order', 'status', 'art'])
                ->make(true);
        }
    }
    public function DashboardRedTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('status', 'Ausgeliefert')->take(5)->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('art', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->rawColumns(['order', 'status', 'art'])
                ->make(true);
        }
    }
    public function DashboardYellowTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('status', 'In Bearbeitung')->take(5)->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('art', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->rawColumns(['order', 'status', 'art'])
                ->make(true);
        }
    }
    public function DashboardBlueTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('status', 'Änderung')->take(5)->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->addColumn('status', function ($row) {

                    $status = '';
                    if ($row->status == "Offen") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-open"></div><div class="status_text">Offen</div></div>';
                    } else if ($row->status == "In Bearbeitung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-progress"></div><div class="status_text">In Bearbeitung</div></div>';
                    } else if ($row->status == "Ausgeliefert") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-delivered"></div><div class="status_text">Ausgeliefert</div></div>';
                    } else if ($row->status == "Änderung") {
                        $status = '<div class="status-wrapper"><div class="status-sphere-change"></div><div class="status_text">Änderung</div></div>';
                    }
                    return $status;
                })
                ->addColumn('art', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->rawColumns(['order', 'status', 'art'])
                ->make(true);
        }
    }
    public function EmPayment(Request $request)
    {
        $temp_order = TempOrder::orderBy('id', 'desc')->get();
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('type', 'Embroidery')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('counting_number', function ($row) use ($temp_order) {
                    $btn = '';
                    foreach ($temp_order as $temp) {
                        if ($temp->order_id == $row->id) {
                            $btn = '<div style="text-align:center;">' . $temp->count_number . '</div>';
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['order', 'date', 'type', 'deliver_time', 'counting_number'])
                ->make(true);
        }
    }
    public function EmPaymentSum(Request $request)
    {
        $count_numebr = 0;
        $temp_order = TempOrder::where('type', 'Embroidery')->get();
        $count_numebr = $temp_order->sum('count_number');
        return response()->json($count_numebr);
    }

    public function EmPaymentHandle(Request $request)
    {
        $temp_orders = TempOrder::where('type', 'Embroidery')->get();
        foreach ($temp_orders as $temp_order) {
            $order = Order::findOrfail($temp_order->order_id);
            $order->count_number = $temp_order->count_number;
            $order->save();
            $temp_order->delete();
        }
    }
    public function VePayment(Request $request)
    {
        $temp_order = TempOrder::orderBy('id', 'desc')->get();
        if ($request->ajax()) {
            $data = Order::orderBy('created_at', 'desc')->where('type', 'Vector')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('counting_number', function ($row) use ($temp_order) {
                    $btn = '';
                    foreach ($temp_order as $temp) {
                        if ($temp->order_id == $row->id) {
                            $btn = '<div style="text-align:center;">' . $temp->count_number . '</div>';
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['order', 'date', 'type', 'deliver_time', 'counting_number'])
                ->make(true);
        }
    }
    public function VePaymentSum(Request $request)
    {
        $count_numebr = 0;
        $temp_order = TempOrder::where('type', 'Vector')->get();
        $count_numebr = $temp_order->sum('count_number');
        return response()->json($count_numebr);
    }
    public function VePaymentHandle(Request $request)
    {
        $temp_orders = TempOrder::where('type', 'Vector')->get();
        foreach ($temp_orders as $temp_order) {
            $order = Order::findOrfail($temp_order->order_id);
            $order->count_number = $temp_order->count_number;
            $order->save();
            $temp_order->delete();
        }
    }
    public function OrderCount(Request $request)
    {
        $order = Order::findOrfail($request->post('order_id'));
        TempOrder::where('order_id', $request->post('order_id'))->delete();
        $temp_order = new TempOrder();
        $temp_order->order_id = $request->post('order_id');
        $temp_order->type = $order->type;
        $temp_order->count_number = $request->post('count_number');
        $temp_order->save();
    }
    public function Parameter(Request $request)
    {
        $order = Order::findOrfail($request->get("id"));
        $em_parameter = CustomerEmParameter::where('customer_id', $order->user_id)->first();
        $ve_parameter = CustomerVeParameter::where('customer_id', $order->user_id)->first();
        return response()->json([$em_parameter, $ve_parameter]);
    }
    public function EmbroideryPaymentArchive(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->whereNotNull('count_number')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('counting_number', function ($row) {
                    $btn = '<div style="text-align:center;">' . $row->count_number . '</div>';
                    return $btn;
                })
                ->rawColumns(['order', 'date', 'type', 'deliver_time', 'counting_number'])
                ->make(true);
        }
    }
    public function VectorPaymentArchive(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->whereNotNull('count_number')->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('order', function ($row) {
                    $order = $row->customer_number . '-' . $row->order_number;
                    return $order;
                })
                ->editColumn('date', function ($row) {
                    $timezone = new DateTimeZone('Europe/Berlin');
                    $date = $row->created_at->setTimezone($timezone)->format('d.m.Y H:i');
                    return $date;
                })
                ->addColumn('type', function ($row) {
                    $type = '';
                    if ($row->type == "Embroidery") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon"></div>';

                    } else if ($row->type == "Vector") {
                        $type = '<div style="text-align:center"><img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon"></div>';
                    }
                    return $type;
                })
                ->addColumn('counting_number', function ($row) {
                    $btn = '<div style="text-align:center;">' . $row->count_number . '</div>';
                    return $btn;
                })
                ->rawColumns(['order', 'date', 'type', 'deliver_time', 'counting_number'])
                ->make(true);
        }
    }
    public function ChangeAvatar(Request $request)
    {
        $customer = User::findOrfail($request->post('customer_id'));
        $avatar_file = $request->file('avatar');
        $upload_dir = 'public/';
        $folder = 'customer-avatar';
        $avatar_filename = $avatar_file->getClientOriginalName();
        if (strlen($avatar_file->getClientOriginalName()) != 1) {
            Storage::makeDirectory($upload_dir);
            if ($avatar_file->storeAs($folder, $avatar_filename, 'public')) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $avatar_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/storage' . '/' . $folder . '/' . $avatar_filename;
                $fullPath = '/public' . '/' . $folder . '/' . $avatar_filename;
                $file_path = Storage::path($fullPath);
                echo $file_path;
                chmod($file_path, 0755);
                $publicPath = public_path();
                $publicStoragePath = $publicPath . '/storage';
                chmod($publicStoragePath, 0755);
                $customer->image = $avatar_url;
                $customer->save();
            }
        }
    }

    public function adminChatGet(Request $request)
    {
        $last_message = ChatMessage::orderBy('id', 'desc')->first();
        $last_messages = ChatMessage::where('chat_id', $last_message->chat_id)->orderBy('id', 'asc')->get();
        return response()->json($last_messages);
    }
    public function adminChatLongPolling(Request $request)
    {
        $lastMessageId = $request->input('adminLastMessageId');
        $last_message = ChatMessage::orderBy('id', 'desc')->first();
        do {
            $messages = ChatMessage::where('id', '>', $lastMessageId)->where('chat_id', $last_message->chat_id)->orderBy('id', 'asc')->get();
            if ($messages->isNotEmpty()) {
                return response()->json($messages);
            }
            usleep(1000000); // Wait for 1 second before checking again
        } while (true);
    }
    public function adminChat(Request $request)
    {
        $message = $request->post('message');
        $chat_id = $request->post('chat_id');
        $chat = ChatMessage::where('chat_id', $chat_id)->orderBy('id', 'desc')->first();

        $chat_message = new ChatMessage();
        $chat_message->chat_id = $chat_id;
        $chat_message->chat_type = $chat->chat_type;
        $chat_message->send_id = 1;
        if ($chat->customer_number != null) {
            $chat_message->customer_number = $chat->customer_number;
        }
        $chat_message->message = $message;
        $chat_message->save();
        return response()->json($chat_message);
    }

    public function adminReceiveChat(Request $request)
    {
        \Log::info("This is slack", $request->all());
        $requestData = $request->all();
        if ($requestData['event']['type'] == 'message') {
            $message = $requestData['event']['text'];
        }
        \Log::info("This is slack", ['message' => $message]);
    }
}
