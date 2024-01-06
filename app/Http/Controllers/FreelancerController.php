<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrderChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use App\Models\CustomerEmParameter;
use App\Models\CustomerVeParameter;
use App\Models\TempOrder;
use App\Models\Chat;
use App\Models\ChatMessage;
use Intervention\Image\Facades\Image;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\DeliveryFile;
use Illuminate\Support\Facades\Validator;
use DateTimeZone;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Mail;
use App\Mail\FreelancerEmbroideryPaymentMail;
use App\Mail\FreelancerVectorPaymentMail;
use App\Mail\ChangeEmFreelacnerAdminMail;
use App\Mail\ChangeEmFreelacnerEndJobAdminMail;
use App\Mail\ChangeVeFreelacnerAdminMail;
use App\Mail\ChangeVeFreelacnerEndJobAdminMail;
use jeremykenedy\Slack\Client as SlackClient;


use DataTables;

use App\Models\Order_file_upload;
use App\Models\Customer_parameter;
use Illuminate\Support\Env;

use function PHPUnit\Framework\returnCallback;

class FreelancerController extends Controller
{
    public function freelancerloginpage()
    {
        return view('freelancer.login');
    }

    public function adminloginpage11()
    {
        return view('admin.login');
    }

    public function freelancerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',

        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            if (Auth::user()->user_type == "freelancer") {
                return redirect('/en');
            } else {
                Auth::logout();
                return redirect(__('routes.freelancer-login'))->with('danger', 'Oops! You do not have the required access permission');
            }
        }
        return redirect(__('routes.freelancer-login'))->with('danger', 'Oppes! You have entered invalid credentials');
    }

    public function goFreelancerLogin()
    {
        return redirect()->route('freelancer-login', ['locale' => 'en']);
    }


    public function freelancerLogout()
    {
        Session::flush();
        Auth::logout();

        return Redirect(__('routes.freelancer-login'));
    }
    public function EmChangeAvatar()
    {
        return view('freelancer.embroidery-change-avatar');
    }
    public function VeChangeAvatar()
    {
        return view('freelancer.vector-change-avatar');
    }
    public function EmChangEAvatarHandle(Request $request)
    {
        $avatar_file = $request->file('change_avatar');
        $upload_dir = 'public/';
        $folder = 'freelancer-avatar';
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
                $freelancer = User::where('user_type', 'freelancer')->where('category_id', '1')->first();
                $freelancer->image = $avatar_url;
                $freelancer->save();
            }
        }
        return redirect('/en');
    }
    public function VeChangEAvatarHandle(Request $request)
    {
        $avatar_file = $request->file('change_avatar');
        $upload_dir = 'public/';
        $folder = 'freelancer-avatar';
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
                $freelancer = User::where('user_type', 'freelancer')->where('category_id', '2')->first();
                $freelancer->image = $avatar_url;
                $freelancer->save();
            }
        }
        return redirect('/en');
    }

    public function viewOrder(Request $request)
    {
        $authuser = auth()->user()->id;
        if ($request->status_filter != '') {
            if ($request->status_filter == 'pending') {
                $data = Order::with('Order_address', 'category', 'Orderfile_uploads')->orderBy('id', 'desc')->where('status', 'pending')->get();
                return Datatables::of($data)->addIndexColumn()
                    ->editColumn('catgory', function ($row) {
                        $category = $row->category->category_name;
                        return $category;
                    })
                    ->editColumn('company', function ($row) {
                        $company = $row->order_address->company;
                        return $company;
                    })
                    ->editColumn('user', function ($row) {
                        $user = $row->user->name;
                        return $user;
                    })
                    ->editColumn('date', function ($row) {
                        $date = $row->created_at->format('Y-m-d');
                        return $date;
                    })
                    ->addColumn('detail', function ($row) {
                        $btn = '<a style="text-decoration:none;" class="btn btn-secondary btn-sm" href=' . __("routes.freelancer-orderdetails") . $row->id . '>Order deatils</a>';
                        return $btn;
                    })
                    ->addColumn('upload', function ($row) {
                        $btn = '<a href=' . __("routes.delivery-files") . $row->id . ' class="btn btn-secondary btn-sm"> Upload Files </a>';
                        return $btn;
                    })
                    ->rawColumns(['catgory', 'upload', 'user', 'date', 'company', 'detail'])
                    ->make(true);
            }
            if ($request->status_filter == 'delivered') {
                $data = Order::with('Order_address', 'category', 'Orderfile_uploads')->orderBy('id', 'desc')->where('status', 'delivered')->get();
            }
            return Datatables::of($data)->addIndexColumn()
                ->editColumn('catgory', function ($row) {
                    $category = $row->category->category_name;
                    return $category;
                })
                ->editColumn('company', function ($row) {
                    $company = $row->order_address->company;
                    return $company;
                })
                ->editColumn('date', function ($row) {
                    $date = $row->created_at->format('Y-m-d');
                    return $date;
                })
                ->editColumn('user', function ($row) {
                    $user = $row->user->name;
                    return $user;
                })
                ->editColumn('selection', function ($row) {
                    $date = __($row->selection);
                    return $date;
                })
                ->addColumn('detail', function ($row) {
                    $btn = '<a style="text-decoration:none;" class="btn btn-secondary btn-sm" href=' . __("routes.freelancer-orderdetails") . $row->id . '>Order deatils</a>';
                    return $btn;
                })
                // ->addColumn('action', function ($row) {
                //     $btn =  '<div class="d-flex" style="gap:20px;">
                //                 <div><a href=""><i class="fa-solid fa-circle-xmark" style="color: #d41616;"></i></a></div>
                //                 <div><a href=""><i class="fa-solid fa-circle-check" style="color: #0d8604;"></i></a></div>
                //             </div>';
                //     return $btn;
                // })
                ->addColumn('upload', function ($row) {
                    $btn = '<a href=' . __("routes.delivery-files") . $row->id . ' class="btn btn-secondary btn-sm"> Upload Files </a>';
                    return $btn;
                })
                ->rawColumns(['catgory', 'upload', 'user', 'date', 'company', 'detail'])
                ->make(true);
        } else {
            if ($request->ajax()) {
                $data = Order::with('Order_address', 'category', 'Orderfile_uploads')->orderBy('id', 'desc')->where('assigned_to', $authuser)->get();
                return Datatables::of($data)->addIndexColumn()
                    ->editColumn('catgory', function ($row) {
                        $category = $row->category->category_name;
                        return $category;
                    })
                    ->editColumn('user', function ($row) {
                        $user = $row->user->name;
                        return $user;
                    })
                    ->editColumn('company', function ($row) {
                        $company = $row->Order_address->company;
                        return $company;
                    })
                    ->editColumn('date', function ($row) {
                        $date = $row->created_at->format('Y-m-d');
                        return $date;
                    })
                    ->editColumn('selection', function ($row) {
                        $date = __($row->selection);
                        return $date;
                    })
                    ->addColumn('detail', function ($row) {
                        $btn = '<a style="text-decoration:none;" class="btn btn-secondary btn-sm" href=' . __("routes.freelancer-orderdetails") . $row->id . '>Order deatils</a>';
                        return $btn;
                    })
                    // ->addColumn('action', function ($row) {

                    //     $btn =  '<div class="d-flex" style="gap:20px;">
                    //                 <div><a href=""><i class="fa-solid fa-circle-xmark" style="color: #d41616;"></i></a></div>
                    //                 <div><a href=""><i class="fa-solid fa-circle-check" style="color: #0d8604;"></i></a></div>
                    //             </div>';
                    //     return $btn;
                    // })
                    ->addColumn('upload', function ($row) {
                        $btn = '<a href=' . __("routes.delivery-files") . $row->id . ' class="btn btn-secondary btn-sm"> Upload Files </a>';
                        return $btn;
                    })
                    ->rawColumns(['catgory', 'upload', 'user', 'date', 'company', 'detail'])
                    ->make(true);
            }
        }
        return view('freelancer.orders.vieworders');
    }

    // public function orderDetails($locale, $id)
    // {
    //     $authuser = auth()->user()->id;
    //     $order = Order::with('Order_address', 'Orderfile_uploads', 'Orderfile_formats')->where('id', $id)->first();
    //     return view('common.orderdetails', compact('order'));
    // }

    public function downloadAddressFIle(Request $request)
    {
        $uploadedFile = $request->image;
        $jpgImage = Image::make($uploadedFile)->encode('jpg');
        // $response = response()->streamDownload(function () use ($jpgImage) {
        //     echo $jpgImage;
        // }, $filename . '.jpg');

        // return $response->header('Content-Type', 'image/jpeg');
    }


    public function downloadFile(Request $request)
    {
        $file = $request->addressfile;
        $file_path = public_path() . "/orders/$file";

        $headers = array(
            'Content-Type: image/jpeg',
        );
        return Response::download($file_path, $file, $headers);
    }

    public function checkFiles(Request $request)
    {
        $id = $request->id;
        $category = $request->category;
        $ordersfiles = Order_file_upload::where('order_id', $id)->get();
        // $ordersfiles = Order::with('Orderfile_uploads')->where('id', $id)->first();
        return response()->json([
            'data' => $ordersfiles
        ]);
    }

    public function freelancerProfile()
    {
        $authuser = auth()->user()->id;
        $user = User::where('id', $authuser)->first();
        return view('freelancer.profile.profile', compact('user'));
    }
    public function Profileupdate(Request $request)
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

    public function changePassword()
    {
        return view('freelancer.changepassword');
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
            return redirect(__('/en'));
        } else {
            session()->flash('message', 'Old password does not matched');
            return redirect(__('routes.freelancer-changepassword'));
        }
    }

    public function filtersData()
    {
        dd('ok');
    }
    public function EmbroideryFreelancerGreenTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Offen')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
    public function EmbroideryFreelancerYellowTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'In Bearbeitung')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
    public function EmbroideryFreelancerRedTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Ausgeliefert')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function EmbroideryFreelancerBlueTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Änderung')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                                        <button onclick="EmbroideryDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
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
    public function EmbroideryFreelancerAllTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                                        <button onclick="EmbroideryDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
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
    public function EmbroideryFreelancerGreenDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Offen')->take(5)->get();
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
    public function EmbroideryFreelancerYellowDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'In Bearbeitung')->take(5)->get();
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
    public function EmbroideryFreelancerRedDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Ausgeliefert')->take(5)->get();
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
    public function EmbroideryFreelancerBlueDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->where('status', 'Änderung')->take(5)->get();
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
                ->addColumn('request', function ($row) {
                    $req = '';

                    if ($row->status == 'Änderung') {
                        $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="EmbroideryDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
                                    </div>
                                </div>
                            ';
                    }
                    return $req;
                })

                ->rawColumns(['order', 'status', 'art', 'request'])
                ->make(true);
        }
    }
    public function getRequestDetail(Request $request)
    {
        $order = Order::findOrfail($request->get('id'));
        $order_change = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'asc')->get();
        $order_file_uploads = Order_file_upload::where('order_id', $request->get('id'))->pluck('base_url');
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->count();
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $en_order = $translator->translate($order);
        $en_order_change = $translator->translate($order_change);
        return response()->json(['order' => $order, 'order_change' => $order_change, 'detail' => $order_file_uploads, 'change_count' => $folderCount, 'en_order' => $en_order, 'en_order_change' => $en_order_change]);
    }
    public function EmOrderDetail(Request $request)
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
                        $folder = explode('/', $change_item->base_url)[3];
                        $folder_key = explode(' ', $folder)[0];
                        if ($folder_key == "Stickprogramm" || $folder_key == "Vektordatei") {
                            $btn = '<button onClick = DeleteEmRequestFile(' . $row->id . ') style="border:none; background:inherit;"><i class="fa-solid fa-trash-can" style="color:#c4ae79;"></i></button>';
                        }
                    }

                    return $btn;
                })
                ->rawColumns(['customer_number', 'order_number', 'download', 'delete'])
                ->make(true);
        }
    }
    public function VeOrderDetail(Request $request)
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
                        $folder = explode('/', $change_item->base_url)[3];
                        $folder_key = explode(' ', $folder)[0];
                        if ($folder_key == "Stickprogramm" || $folder_key == "Vektordatei") {
                            $btn = '<button onClick = DeleteVeRequestFile(' . $row->id . ') style="border:none; background:inherit;"><i class="fa-solid fa-trash-can" style="color:#c4ae79;"></i></button>';
                        }
                    }

                    return $btn;
                })
                ->rawColumns(['customer_number', 'order_number', 'download', 'delete'])
                ->make(true);
        }
    }

    public function VectorFreelancerGreenTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Offen')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function VectorFreelancerYellowTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'In Bearbeitung')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function VectorFreelancerRedTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Ausgeliefert')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                ->rawColumns(['order', 'date', 'detail', 'status', 'type', 'deliver_time'])
                ->make(true);
        }
    }
    public function VectorFreelancerBlueTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Änderung')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                                        <button onclick="VectorDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
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
    public function VectorFreelancerAllTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->get();
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

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none; " onclick="freeOpenOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                                        <button onclick="VectorDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
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
    public function VectorFreelancerGreenDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Offen')->take(5)->get();
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
    public function JobFileUpload(Request $request)
    {
        $order_id = $request->post('free_detail_id');
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
    public function EmbroideryFileUpload(Request $request)
    {
        $freelancer_request_id = $request->post('embroidery_request_id');
        $order = Order::findOrfail($freelancer_request_id);
        $customer = User::findOrfail($order->user_id);
        $time = $request->post('embroidery_time');

        OrderChange::where('time', $time)->delete();
        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer_em' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer_em' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order->id;
        $order_change->change_number = $change_number + 1;
        $order_change->time = $time;
        $order_change->changed_from = "freelancer_em";
        $order_change->save();


        $files = $request->file("files");
        $uploadDir = 'public/';
        $filePath = $order->customer_number . '/' .
            $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/';
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'freelancer_em')->count();
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
    public function VectorFileUpload(Request $request)
    {
        $freelancer_request_id = $request->post('vector_request_id');
        $order = Order::findOrfail($freelancer_request_id);
        $customer = User::findOrfail($order->user_id);
        $time = $request->post('vector_time');

        OrderChange::where('time', $time)->delete();
        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer_ve' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'freelancer_ve' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order->id;
        $order_change->change_number = $change_number + 1;
        $order_change->time = $time;
        $order_change->changed_from = "freelancer_ve";
        $order_change->save();

        $files = $request->file("files");
        $uploadDir = 'public/';
        $filePath = $order->customer_number . '/' .
            $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/';
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'freelancer_ve')->count();
        $folderName = 'Vektordatei Änderung' . ($folderCount) . '/';
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
    public function VectorFreelancerYellowDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'In Bearbeitung')->take(5)->get();
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
    public function VectorFreelancerRedDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Ausgeliefert')->take(5)->get();
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
    public function VectorFreelancerBlueDashboardTable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->where('status', 'Änderung')->take(5)->get();
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
                ->addColumn('request', function ($row) {
                    $req = '';

                    if ($row->status == 'Änderung') {
                        $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="VectorDetailRequest(' . $row->id . ', \'Originaldatei\')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
                                    </div>
                                </div>
                            ';
                    }
                    return $req;
                })

                ->rawColumns(['order', 'status', 'art', 'request'])
                ->make(true);
        }
    }
    public function FreelancerOrderDetail(Request $request)
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
                        $folder = explode('/', $change_item->base_url)[3];
                        if ($folder == "Stickprogramm" || $folder == "Vektordatei") {
                            $btn = '<button onClick = DeleteFile(' . $row->id . ') style="border:none; background:inherit;"><i class="fa-solid fa-trash-can" style="color:#c4ae79;"></i></button>';
                        }
                    }

                    return $btn;
                })


                ->rawColumns(['customer_number', 'order_number', 'download', 'delete'])
                ->make(true);
        }
    }
    public function DeleteFile($local, $id)
    {
        $delete_file = Order_file_upload::findOrfail($id);
        $delete_file->delete();
    }
    public function EmDeleteFile($local, $id)
    {
        $delete_file = Order_file_upload::findOrfail($id);
        $delete_file->delete();
    }
    public function VeDeleteFile($local, $id)
    {
        $delete_file = Order_file_upload::findOrfail($id);
        $delete_file->delete();
    }
    public function FreelancergetOrderDetail(Request $request)
    {
        $order = Order::findOrfail($request->get('id'));
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $en_order = $translator->translate($order);
        $order_file_uploads = Order_file_upload::where('order_id', $request->get('id'))->pluck('base_url');
        return response()->json(['order' => $order, 'detail' => $order_file_uploads, 'en_order' => $en_order]);
    }
    public function StartJob(Request $request)
    {
        $order = Order::findOrfail($request->get('start_job_id'));
        $order->status = "In Bearbeitung";
        $order->save();
    }
    public function EndJob(Request $request)
    {
        $order = Order::findOrfail($request->get('end_job_id'));
        $order_upload = Order_file_upload::where('order_id', $request->get('end_job_id'))->pluck('base_url');
        $base_url_array = explode('","', $order_upload);
        foreach ($base_url_array as $base_url) {
            $folderArray = explode('\/', $base_url);
            $folder = $folderArray[3];
            if ($folder == 'Stickprogramm' || $folder == 'Vektordatei') {
                $order->status = "Ausgeliefert";
                $order->save();
            }
            // else {
            //     return response()->json(['message' => 'Error'], 500);
            // }
        }
    }
    public function EmbroideryEndChange(Request $request)
    {
        $order = Order::findOrfail($request->get('end_change_id'));
        $order_change_count = OrderChange::where('order_id', $order->id)->where('changed_from', 'freelancer_em')->count();
        if ($order_change_count > 0) {
            $order->status = 'Ausgeliefert';
            $order->save();
        } else {
            return response()->json(['message' => 'Error'], 500);
        }
    }
    public function VectorEndChange(Request $request)
    {
        $order = Order::findOrfail($request->get('ve_end_change_id'));
        $order_change_count = OrderChange::where('order_id', $order->id)->where('changed_from', 'freelancer_ve')->count();
        if ($order_change_count > 0) {
            $order->status = 'Ausgeliefert';
            $order->save();
            OrderChange::where('order_id', $order->id)->delete();
        } else {
            return response()->json(['message' => 'Error'], 500);
        }
    }
    public function Parameter(Request $request)
    {
        $order = Order::findOrfail($request->get("id"));
        $ge_em_parameter = CustomerEmParameter::where('customer_id', $order->user_id)->first();
        $ge_ve_parameter = CustomerVeParameter::where('customer_id', $order->user_id)->first();
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $em_parameter = $translator->translate($ge_em_parameter);
        $ve_parameter = $translator->translate($ge_ve_parameter);
        return response()->json([$em_parameter, $ve_parameter]);
    }
    public function EmbroideryFreelancerPaymentTable(Request $request)
    {
        $temp_order = TempOrder::orderBy('id', 'desc')->get();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Embroidery')->get();
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

    public function VectorFreelancerPaymentTable(Request $request)
    {
        $temp_order = TempOrder::orderBy('id', 'desc')->get();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('type', 'Vector')->get();
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
    public function FreelancerOrderCount(Request $request)
    {
        $order = Order::findOrfail($request->post('order_id'));
        TempOrder::where('order_id', $request->post('order_id'))->delete();
        $temp_order = new TempOrder();
        $temp_order->order_id = $request->post('order_id');
        $temp_order->type = $order->type;
        $temp_order->count_number = $request->post('count_number');
        $temp_order->save();
    }
    public function EmbroideryPaymentMail(Request $request)
    {
        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        try {
            Mail::to($recipient_admin)->send(new FreelancerEmbroideryPaymentMail());
            Mail::to('christoperw818@gmail.com')->send(new FreelancerEmbroideryPaymentMail());
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function VectorPaymentMail(Request $request)
    {
        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        try {
            Mail::to($recipient_admin)->send(new FreelancerVectorPaymentMail());
            Mail::to('christoperw818@gmail.com')->send(new FreelancerVectorPaymentMail());
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
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
    public function emChat(Request $request)
    {
        $customer_id = auth()->user()->id;
        $customer = User::findOrfail($customer_id);
        $message = $request->post('message');
        $chat = Chat::where('person_id', $customer_id)->first();
        if ($chat === null) {
            $chat_data = new Chat();
            $chat_data->admin_id = 1;
            $chat_data->person_id = $customer_id;
            $chat_data->save();
            $chat_message = new ChatMessage();
            $chat_message->chat_id = $chat_data->id;
            $chat_message->chat_type = 'em_freelancer';
            $chat_message->send_id = $customer_id;
            $chat_message->message = $message;
            $chat_message->save();
        } else {
            $chat_message = new ChatMessage();
            $chat_message->chat_id = $chat->id;
            $chat_message->chat_type = 'em_freelancer';
            $chat_message->send_id = $customer_id;
            $chat_message->message = $message;
            $chat_message->save();
        }
        $token = 'xoxb-5817937631651-6432446406258-n6lNZ0Xl2gcek2YMx80jmvl1';
        $channel = 'D06CQ9U2LAF';  // The ID of the channel or user where you want to send the message
        $payload = [
            "token" => $token,
            "channel" => $channel,
            "text" => $message,
            'username' => 'Stickerei-Freiberufler',
            'icon_url' => $customer->image,
        ];

        $ch = curl_init('https://slack.com/api/chat.postMessage');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);
        return response()->json($chat_message);
    }
    public function emRecieveSlackMessage(Request $request)
    {

        \Log::info("This is em freelacner slack", $request->all());
        $requestData = $request->all();
        if ($requestData['event']['type'] == 'message') {
            $message = $requestData['event']['text'];
            if (isset($requestData['event']['client_msg_id']) && $requestData['event']['channel'] == 'D06CQ9U2LAF') {
                $chat_message = new ChatMessage();
                $chat_message->chat_id = 12;
                $chat_message->chat_type = 'em_freelancer';
                $chat_message->send_id = 1;
                $chat_message->message = $message;
                $chat_message->save();
            }
        }
    }
    public function veRecieveSlackMessage(Request $request)
    {
        \Log::info("This is ve vector slack", $request->all());
        $requestData = $request->all();
        if ($requestData['event']['type'] == 'message') {
            $message = $requestData['event']['text'];
            if (isset($requestData['event']['client_msg_id']) && $requestData['event']['channel'] == 'D06CHPYQR8W') {
                $chat_message = new ChatMessage();
                $chat_message->chat_id = 13;
                $chat_message->chat_type = 've_freelancer';
                $chat_message->send_id = 1;
                $chat_message->message = $message;
                $chat_message->save();
            }
        }
    }
    public function veChat(Request $request)
    {
        $customer_id = auth()->user()->id;
        $customer = User::findOrfail($customer_id);
        $message = $request->post('message');
        $chat = Chat::where('person_id', $customer_id)->first();
        if ($chat === null) {
            $chat_data = new Chat();
            $chat_data->admin_id = 1;
            $chat_data->person_id = $customer_id;
            $chat_data->save();
            $chat_message = new ChatMessage();
            $chat_message->chat_id = $chat_data->id;
            $chat_message->chat_type = 've_freelancer';
            $chat_message->send_id = $customer_id;
            $chat_message->message = $message;
            $chat_message->save();
        } else {
            $chat_message = new ChatMessage();
            $chat_message->chat_id = $chat->id;
            $chat_message->chat_type = 've_freelancer';
            $chat_message->send_id = $customer_id;
            $chat_message->message = $message;
            $chat_message->save();
        }
        $token = 'xoxb-5817937631651-6445086705457-Xie96IlviGQvCPgCXPgW2xcC';
        $channel = 'D06CHPYQR8W';  // The ID of the channel or user where you want to send the message
        $payload = [
            "token" => $token,
            "channel" => $channel,
            "text" => $message,
            'username' => 'Vektor-Freiberufler',
            'icon_url' => $customer->image,
        ];

        $ch = curl_init('https://slack.com/api/chat.postMessage');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);
        return response()->json($chat_message);
    }
    public function ChatGet(Request $request)
    {
        $customer_id = auth()->user()->id;
        $chat = Chat::where('person_id', $customer_id)->first();
        if ($chat != null) {
            $message = ChatMessage::where('chat_id', $chat->id)->orderBy('id', 'desc')->get();
            return response()->json($message);
        }
    }
    public function emFreelancerChatLongPolling(Request $request)
    {
        $lastMessageId = $request->input('emFreelancerLastMessageId');
        $customer_id = auth()->user()->id;
        $chat = Chat::where('person_id', $customer_id)->first();
        do {
            $messages = ChatMessage::where('id', '>', $lastMessageId)->where('chat_id', $chat->id)->orderBy('id', 'asc')->get();
            if ($messages->isNotEmpty()) {
                return response()->json($messages);
            }
            usleep(1000000); // Wait for 1 second before checking again
        } while (true);
    }
    public function veFreelancerChatLongPolling(Request $request)
    {
        $lastMessageId = $request->input('veFreelancerLastMessageId');
        $customer_id = auth()->user()->id;
        $chat = Chat::where('person_id', $customer_id)->first();
        do {
            $messages = ChatMessage::where('id', '>', $lastMessageId)->where('chat_id', $chat->id)->orderBy('id', 'asc')->get();
            if ($messages->isNotEmpty()) {
                return response()->json($messages);
            }
            usleep(1000000); // Wait for 1 second before checking again
        } while (true);
    }
    public function EndJobMail(Request $request)
    {
        $order = Order::findOrfail($request->get('order_id'));
        $customer = User::findOrfail($order->user_id);
        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        $recipient_customer = $customer->email;
        if ($order->type == 'Embroidery') {
            $files = [];
            $attachmanet_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
            foreach ($attachmanet_files as $attachmant) {
                $customer_number = explode('/', $attachmant)[1];
                $project_name = explode('/', $attachmant)[2];
                $folder_name = explode('/', $attachmant)[3];
                $filename = explode('/', $attachmant)[4];
                if ($folder_name == 'Stickprogramm') {
                    $files[] = 'public/' . $customer_number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
                }
            }
            try {
                Mail::to($recipient_admin)->send(new ChangeEmFreelacnerEndJobAdminMail($order, $customer, $files));
                // Mail::to($recipient_customer)->send(new ChangeEmFreelacnerEndJobAdminMail($order, $customer, $files));
                Mail::to('christoperw818@gmail.com')->send(new ChangeEmFreelacnerEndJobAdminMail($order, $customer, $files));
                Mail::to('habedere@sinzers.de')->send(new ChangeEmFreelacnerEndJobAdminMail($order, $customer, $files));
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        } else if ($order->type == 'Vector') {
            $files = [];
            $attachmanet_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
            foreach ($attachmanet_files as $attachmant) {
                $customer_number = explode('/', $attachmant)[1];
                $project_name = explode('/', $attachmant)[2];
                $folder_name = explode('/', $attachmant)[3];
                $filename = explode('/', $attachmant)[4];
                if ($folder_name == 'Vektordatei') {
                    $files[] = 'public/' . $customer_number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
                }
            }

            try {
                Mail::to($recipient_admin)->send(new ChangeVeFreelacnerEndJobAdminMail($order, $customer, $files));
                // Mail::to($recipient_customer)->send(new ChangeVeFreelacnerEndJobAdminMail($order, $customer, $files));
                Mail::to('christoperw818@gmail.com')->send(new ChangeVeFreelacnerEndJobAdminMail($order, $customer, $files));
                Mail::to('habedere@sinzers.de')->send(new ChangeVeFreelacnerEndJobAdminMail($order, $customer, $files));
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        }
    }

    public function emChangeMail(Request $request)
    {
        $order = Order::findOrfail($request->get('order_id'));
        $customer = User::findOrfail($order->user_id);
        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        $recipient_customer = $customer->email;

        $files = [];
        $folder_name = [];
        $attachment_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
        foreach ($attachment_files as $attachmant) {
            $folder_name[] = explode('/', $attachmant)[3];
        }
        $maxFolderNumber = -1;

        foreach ($folder_name as $name) {
            if (strpos($name, 'Stickprogramm Änderung') === 0) {
                $folderNumber = (int) substr($name, strlen('Stickprogramm Änderung'));
                if ($folderNumber > $maxFolderNumber) {
                    $maxFolderNumber = $folderNumber;
                }
            }
        }
        foreach ($attachment_files as $attachmant) {
            $customer_Number = explode('/', $attachmant)[1];
            $project_name = explode('/', $attachmant)[2];
            $folder_name = explode('/', $attachmant)[3];
            $filename = explode('/', $attachmant)[4];
            if (strpos($folder_name, 'Stickprogramm Änderung') === 0) {
                $folderNumber = (int) substr($folder_name, strlen('Stickprogramm Änderung'));

                if ($folderNumber == $maxFolderNumber) {
                    $files[] = 'public/' . $customer_Number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
                }
            }
        }
        // var_dump($files);
        // die();
        try {
            Mail::to($recipient_admin)->send(new ChangeEmFreelacnerAdminMail($order, $customer, $files));
            // Mail::to($recipient_customer)->send(new ChangeEmFreelacnerAdminMail($order, $customer, $files));
            Mail::to('christoperw818@gmail.com')->send(new ChangeEmFreelacnerAdminMail($order, $customer, $files));
            Mail::to('habedere@sinzers.de')->send(new ChangeEmFreelacnerAdminMail($order, $customer, $files));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
    public function veChangeMail(Request $request)
    {
        $order = Order::findOrfail($request->get('order_id'));
        $customer = User::findOrfail($order->user_id);
        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        $recipient_customer = $customer->email;

        $files = [];
        $folder_name = [];
        $attachment_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
        foreach ($attachment_files as $attachmant) {
            $folder_name[] = explode('/', $attachmant)[3];
        }
        $maxFolderNumber = -1;

        foreach ($folder_name as $name) {
            if (strpos($name, 'Vektordatei Änderung') === 0) {
                $folderNumber = (int) substr($name, strlen('Vektordatei Änderung'));
                if ($folderNumber > $maxFolderNumber) {
                    $maxFolderNumber = $folderNumber;
                }
            }
        }
        foreach ($attachment_files as $attachmant) {
            $customer_Number = explode('/', $attachmant)[1];
            $project_name = explode('/', $attachmant)[2];
            $folder_name = explode('/', $attachmant)[3];
            $filename = explode('/', $attachmant)[4];
            if (strpos($folder_name, 'Vektordatei Änderung') === 0) {
                $folderNumber = (int) substr($folder_name, strlen('Vektordatei Änderung'));

                if ($folderNumber == $maxFolderNumber) {
                    $files[] = 'public/' . $customer_Number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
                }
            }
        }
        try {
            Mail::to($recipient_admin)->send(new ChangeVeFreelacnerAdminMail($order, $customer, $files));
            // Mail::to($recipient_customer)->send(new ChangeVeFreelacnerAdminMail($order, $customer, $files));
            Mail::to('christoperw818@gmail.com')->send(new ChangeVeFreelacnerAdminMail($order, $customer, $files));
            Mail::to('habedere@sinzers.de')->send(new ChangeVeFreelacnerAdminMail($order, $customer, $files));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }
}
