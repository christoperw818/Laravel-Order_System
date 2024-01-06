<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderChange;
use DateTimeZone;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\category;
use App\Models\Order_address;
use App\Models\Order_file_format;
use App\Models\Order_file_upload;
use App\Models\CustomerEmParameter;
use App\Models\CustomerVeParameter;
use App\Rules\ExcludeFileTypes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSubmitMail;
use App\Mail\OrderUpdateEmail;
use App\Mail\OrderFormMail;
use App\Mail\OrderFormCustomerMail;
use App\Mail\OrderFormFreelancerMail;
use App\Mail\OrderRequestAdmin;
use App\Mail\OrderRequestCustomer;
use App\Mail\OrderRequestFreelancerMail;
use App\Mail\OrderrRequestTextFreelancerMail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\File;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Helper;
use ZipArchive;




class OrderController extends Controller
{
    public function EmbroideryInformation()
    {
        return view('users.orders.embroidery_information');
    }
    public function EmbroideryPrice()
    {
        return view('users.orders.embroidery_price');
    }

    public function embroideryOrderSave(Request $request)
    {
    }

    public function embroideryOrderSumit(Request $request)
    {
        $request->validate([
            'selection' => 'required',
            'delivery_time' => 'required',
            'project_name' => 'required',
            'end_product' => 'required',
            'emb_info' => 'required',
            'emb_size' => 'required',
            'instructions' => 'required',
            'Salutation' => 'required',
            'company' => 'required',
            'full_name' => 'required',
            'address' => 'required',
            'zip_code' => 'required',
            'place' => 'required',
            'vat_no' => 'required',
            'contact_no' => 'required',
            'email' => 'required',
            'site' => 'required',
            'file_format' => 'required',


        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $users = Auth::user()->id;
            $org_id = Auth::user()->org_id;
            $freelancer = User::where('category_id', '1')->first();

            $order = Order::create([
                'project_name' => $data['project_name'],
                'category_id' => '1',
                'user_id' => $users,
                'assigned_to' => $freelancer->id,
                'delivery_time' => $data['delivery_time'],
                'end_product' => $data['end_product'],
                'selection' => $data['selection'],
                'emb_info' => $data['emb_info'],
                'emb_size' => $data['emb_size'],
                'instructions' => $data['instructions'],
                'status' => 'pending',
                'org_id' => $org_id,
            ]);

            $file = $request->file('address_file');
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $finalName = $users . '-' . $order->id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();

            $destination = $users . '-' . $order->id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
            $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                'ContentDisposition' => 'attachment'
            ]);
            $imageUrl = Storage::disk('s3')->url($destination);

            Order_address::create([
                'order_id' => $order->id,
                'Salutation' => $data['Salutation'],
                'company' => $data['company'],
                'full_name' => $data['full_name'],
                'address' => $data['address'],
                'zip_code' => $data['zip_code'],
                'place' => $data['place'],
                'vat_no' => $data['vat_no'],
                'contact_no' => $data['contact_no'],
                'email' => $data['email'],
                'site' => $data['site'],
                'address_file' => $imageUrl,
            ]);

            Order_file_format::create([
                'order_id' => $order->id,
                'file_format' => $data['file_format'],
                'view_file_format' => $data['view_file_format']
            ]);


            if ($request->hasFile('file_upload')) {
                $files = [];
                foreach ($request->file('file_upload') as $file) {
                    $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $uploadFinalName = $users . '-' . $order->id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                    $upload_destination = $users . '-' . $order->id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                    $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                        'ContentDisposition' => 'attachment'
                    ]);
                    $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                    $files[] = $upload_imageUrl;
                }
            }

            $orderfile = new Order_file_upload();
            $orderfile->order_id = $order->id;
            $orderfile->file_upload = json_encode($files);
            $orderfile->save();

            // send mail to admin and freelance
            $data['order_id'] = $order->id;
            $data['user'] = Auth::user();
            Mail::to([env('ADMIN_EMAIL'), $freelancer->email])->send(new OrderSubmitMail($data));

            DB::commit();
            return response()->json(['message' => 'Order is successfully placed']);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['error' => 'Something went wrong.Failed to create order: ' . $e->getMessage()], 500);
        }
    }


    public function VectorInformation()
    {
        return view('users.orders.vector_information');
    }
    public function VectorPrice()
    {
        return view('users.orders.vector_price');
    }


    public function vectorOrderSumit(Request $request)
    {
        $request->validate([
            'selection' => 'required',
            'delivery_time' => 'required',
            'project_name' => 'required',
            'instructions' => 'required',
            'Salutation' => 'required',
            'company' => 'required',
            'full_name' => 'required',
            'address' => 'required',
            'zip_code' => 'required',
            'place' => 'required',
            'vat_no' => 'required',
            'contact_no' => 'required',
            'email' => 'required',
            'site' => 'required',
            'file_format' => 'required',


        ]);

        DB::beginTransaction();
        try {

            $data = $request->all();
            $users = Auth::user()->id;
            $freelancer = User::where('category_id', '2')->first();

            $order = Order::create([
                'project_name' => $data['project_name'],
                'category_id' => '2',
                'user_id' => $users,
                'assigned_to' => $freelancer->id,
                'delivery_time' => $data['delivery_time'],
                'selection' => $data['selection'],
                'instructions' => $data['instructions'],
                'status' => 'pending',
                'org_id' => Auth::user()->org_id
            ]);


            $file = $request->file('address_file');
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $finalName = $users . '-' . $order->id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();
            $destination = $users . '-' . $order->id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
            $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                'ContentDisposition' => 'attachment'
            ]);
            $imageUrl = Storage::disk('s3')->url($destination);

            Order_address::create([
                'order_id' => $order->id,
                'Salutation' => $data['Salutation'],
                'company' => $data['company'],
                'full_name' => $data['full_name'],
                'address' => $data['address'],
                'zip_code' => $data['zip_code'],
                'place' => $data['place'],
                'vat_no' => $data['vat_no'],
                'contact_no' => $data['contact_no'],
                'email' => $data['email'],
                'site' => $data['site'],
                'address_file' => $imageUrl,
            ]);

            Order_file_format::create([
                'order_id' => $order->id,
                'file_format' => $data['file_format'],
                'view_file_format' => $data['view_file_format']
            ]);

            if ($request->hasFile('file_upload')) {
                $files = [];
                foreach ($request->file('file_upload') as $file) {
                    $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $uploadFinalName = $users . '-' . $order->id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                    $upload_destination = $users . '-' . $order->id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                    $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                        'ContentDisposition' => 'attachment'
                    ]);
                    $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                    $files[] = $upload_imageUrl;
                }
            }

            $orderfile = new Order_file_upload();
            $orderfile->order_id = $order->id;
            $orderfile->file_upload = json_encode($files);
            $orderfile->save();

            // send mail to admin and freelance
            $data['order_id'] = $order->id;
            $data['user'] = Auth::user();
            Mail::to([env('ADMIN_EMAIL'), $freelancer->email])->send(new OrderSubmitMail($data));

            DB::commit(); // All good, commit the changes
            return response()->json(['message' => 'Order is successfully placed']);
        } catch (\Exception $e) {
            DB::rollback(); // Something went wrong, rollback the changes

            return response()->json(['error' => 'Something went wrong.Failed to create order ' . $e->getMessage()], 500);
        }
    }

    public function orderDetails($locale, $id)
    {
        $order = Order::with('Order_address', 'Orderfile_uploads', 'Orderfile_formats', 'category')->where('id', $id)->first();
        return view('common.orderdetails', compact('order'));
    }

    public function viewOrder(Request $request)
    {
        $authuser = auth()->user();

        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')
                ->where('user_id', $authuser->id)
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
                        ->where('user_id', $authuser->id)
                        ->where(function ($query) use ($request) {
                            $query->where('project_name', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("CONCAT(customer_number, '-', order_number) LIKE ?", ['%' . $request->order_filter . '%'])
                                ->orWhere('deliver_time', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhere('ordered_from', 'LIKE', '%' . $request->order_filter . '%')
                                ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ['%' . $request->order_filter . '%']);
                        })->get();
                } else {
                    $data = Order::orderBy('id', 'desc')
                        ->where('user_id', $authuser->id)
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
                        ->where('user_id', $authuser->id)
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
                        ->where('user_id', $authuser->id)
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
                        $type = '<img src="' . asset('asset/images/reel-duotone.svg') . '" alt="embroidery" class="dashboard_icon">';

                    } else if ($row->type == "Vector") {
                        $type = '<img src="' . asset('asset/images/bezier-curve-duotone.svg') . '" alt="vector" class="dashboard_icon">';
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
                        $btn = '<button style="border:none; background:none;" onclick="openOrderChangeModal(' . $row->id . ')"><img src="' . asset('asset/images/ÄndernIcon.svg') . '" class="icon_size"></button>';
                    }
                    return $btn;
                })
                ->addColumn('detail', function ($row) {

                    $btn = '<div style="width:100%;text-align:center;"><button style="border:none; background:none;" onclick="openOrderDetailModal(' . $row->id . ', \'Originaldatei\')"><img src="' . asset('asset/images/DetailIcon.svg') . '" alt="order-detail-icon" class="icon_size"></button></div>';
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
                    $delete = '<input class="overview_td_checkbox" type="checkbox" value="' . $row->id . '"/>';
                    return $delete;
                })
                ->addColumn('request', function ($row) use ($order_changes) {
                    $req = '';
                    foreach ($order_changes as $order_change) {
                        if ($order_change->order_id == $row->id) {
                            $req = '
                                <div class="d-flex" style="gap:20px;">
                                    <div style="display: flex; margin:auto;">
                                        <button onclick="showOrderRequest(' . $row->id . ')" style="border:none; background-color:inherit;"><img src="' . asset('asset/images/triangle-person-digging-duotone.svg') . '" class="icon_size"></button>
                                    </div>
                                </div>
                            ';
                        }
                    }
                    return $req;
                })
                ->rawColumns(['order', 'action', 'date', 'detail', 'status', 'type', 'deliver_time', 'delete', 'request'])
                ->make(true);
        }

        // $ordersWithCategory = Order::with(['category','Order_address'])->where('user_id', $authuser)->get();

        return view('users.orders.vieworder');
    }
    public function DeleteOrder(Request $request)
    {
        $delete_id = $request->post('delete_id');
        $delete_ids = explode(',', $delete_id);
        foreach ($delete_ids as $id) {
            $order = Order::findOrfail($id);
            $order->delete();
        }
    }

    public function OrderDetail(Request $request)
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

                ->rawColumns(['customer_number', 'order_number', 'download'])
                ->make(true);
        }
    }

    public function multiple(Request $request, $id)
    {
        //     $zip = new ZipArchive();
        //     $order = Order::findOrFail($id);
        //     $folder = $order->customer_number . '/' . $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/';
        //     $files = Order_file_upload::where('order_id', $id)->get();
        //     $fileName = $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '.zip';
        //     if ($zip->open(storage_path('app/public/' . $folder . $fileName), ZipArchive::CREATE) === true) {
        //         $files = Storage::allFiles('public/' . $folder);
        //         foreach ($files as $key => $file) {
        //             $relativeNameInZipFile = basename('public/' . $folder . $file);
        //             $filePathArray = explode('/', $file);
        //             $zip->addFile(storage_path('app/' . $file), $filePathArray[3] . '/' . $relativeNameInZipFile);
        //         }
        //         $zip->close();
        //     }

        //     return response()->download(storage_path('app/public/' . $folder . $fileName));
        // }
        // public function donwloadByFolder(Request $request) {
        $zip = new ZipArchive();
        $order = Order::findOrFail($id);
        $folder = $order->customer_number . '/' . $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/' . $request->get('type') . '/';
        $files = Order_file_upload::where('order_id', $id)->where('base_url', 'LIKE', '%' . $folder . '%')->get();
        $fileName = $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '.zip';
        if ($zip->open(storage_path('app/public/' . $folder . $fileName), ZipArchive::CREATE) === true) {
            $files = Storage::allFiles('public/' . $folder);
            foreach ($files as $key => $file) {
                $relativeNameInZipFile = basename('public/' . $folder . $file);
                // var_dump($relativeNameInZipFile, storage_path('app/public/' . $folder . $file), $file, $folder);
                // die();
                $filePathArray = explode('/', $file);
                $zip->addFile(storage_path('app/' . $file), $filePathArray[3] . '/' . $relativeNameInZipFile);
            }
            $zip->close();
        }

        return response()->download(storage_path('app/public/' . $folder . $fileName));
    }


    public function getOrderDetail(Request $request)
    {
        $order = Order::findOrfail($request->get('id'));
        $order_file_uploads = Order_file_upload::where('order_id', $request->get('id'))->pluck('base_url');
        $folderCount = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->count();
        return response()->json(['order' => $order, 'detail' => $order_file_uploads, 'change_count' => $folderCount]);
    }
    public function OrderRequest($locale, $id)
    {
        $order = Order::find($id);
        $order_change = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->get();
        return response()->json($order_change);
    }

    public function DashboardGreenTable(Request $request)
    {
        $authuser = auth()->user();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('user_id', $authuser->id)->where('status', 'Offen')->take(5)->get();
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
        $authuser = auth()->user();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('user_id', $authuser->id)->where('status', 'Ausgeliefert')->take(5)->get();
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
        $authuser = auth()->user();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('user_id', $authuser->id)->where('status', 'In Bearbeitung')->take(5)->get();
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
        $authuser = auth()->user();
        if ($request->ajax()) {
            $data = Order::orderBy('id', 'desc')->where('user_id', $authuser->id)->where('status', 'Änderung')->take(5)->get();
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


    public function fileUpload(Request $request)
    {
        $type = $request->post('type');
        $deliver_time = $request->post('deliver_time');
        $project_name = $request->post('project_name');
        $size = $request->post('size');
        $width_height = $request->post('width_height');
        $products = $request->post('products');
        $special_instructions = $request->post('special_instructions');
        $customer_number = $request->post('order_form_customer_number');
        $ordered_from = $request->post('ordered_from');
        $last_order = Order::where('customer_number', $customer_number)->orderBy('order_number', 'desc')->first();

        $order = Order::where('type', $type)
            ->where('project_name', $project_name)
            ->where('size', $size)
            ->where('deliver_time', $deliver_time)
            ->where('width_height', $width_height)
            ->where('products', $products)
            ->where('special_instructions', $special_instructions)
            ->where('ordered_from', $ordered_from)->first();




        if ($order == null) {
            $order = new Order();
            $order->customer_number = $customer_number;
            $order->order_number = $last_order == null ? '0001' : sprintf('%04s', $last_order->order_number + 1);
            $order->project_name = $project_name;
            $order->ordered_from = $ordered_from;
            $order->status = 'Offen';
            $order->type = $type;
            $order->size = $size;
            $order->deliver_time = $deliver_time;
            $order->width_height = $width_height;
            $order->products = $products;
            $order->special_instructions = $special_instructions;
            $order->category_id = 1;
            $order->user_id = auth()->user()->id;
            $order->assigned_to = 4;
            $order->org_id = auth()->user()->id;
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
                $fileName = $order->customer_number . '-' . $order->order_number . '-' . ($key + 1) . '.' . $file->getClientOriginalExtension();
                $exist_file = Order_file_upload::where('base_url', 'LIKE', 'storage/' . $filePath . '%')->orderBy('base_url', 'desc')->first();
                if ($exist_file != null) {
                    $filePathArray = explode('/', $exist_file->base_url);
                    $fileNameArray = explode('-', $filePathArray[4]);
                    $fileExtensionArray = explode('.', $fileNameArray[2]);
                    $index = $fileExtensionArray[0];
                    $index = $index + 1;
                    $fileName = $order->customer_number . '-' . $order->order_number . '-' . $index . '.' . $file->getClientOriginalExtension();
                    if ($file->storeAs($filePath, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $index;
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
                } else {
                    if ($file->storeAs($filePath, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $key + 1;
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
        }
    }
    public function EmployerfileUpload(Request $request)
    {
        $type = $request->post('type');
        $deliver_time = $request->post('deliver_time');
        $project_name = $request->post('project_name');
        $size = $request->post('size');
        $width_height = $request->post('width_height');
        $products = $request->post('products');
        $special_instructions = $request->post('special_instructions');
        $customer = User::findOrfail(auth()->user()->org_id);
        $customer_number = $customer->customer_number;
        $ordered_from = $customer->name;
        $last_order = Order::where('customer_number', $customer_number)->orderBy('order_number', 'desc')->first();

        $order = Order::where('type', $type)
            ->where('project_name', $project_name)
            ->where('size', $size)
            ->where('deliver_time', $deliver_time)
            ->where('width_height', $width_height)
            ->where('products', $products)
            ->where('special_instructions', $special_instructions)
            ->where('ordered_from', $ordered_from)->first();


        if ($order == null) {
            $order = new Order();
            $order->customer_number = $customer_number;
            $order->order_number = $last_order == null ? '0001' : sprintf('%04s', $last_order->order_number + 1);
            $order->project_name = $project_name;
            $order->ordered_from = $ordered_from;
            $order->status = 'Offen';
            $order->type = $type;
            $order->size = $size;
            $order->deliver_time = $deliver_time;
            $order->width_height = $width_height;
            $order->products = $products;
            $order->special_instructions = $special_instructions;
            $order->category_id = 1;
            $order->user_id = auth()->user()->org_id;
            $order->assigned_to = 4;
            $order->org_id = auth()->user()->id;
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
                $fileName = $order->customer_number . '-' . $order->order_number . '-' . ($key + 1) . '.' . $file->getClientOriginalExtension();
                $exist_file = Order_file_upload::where('base_url', 'LIKE', 'storage/' . $filePath . '%')->orderBy('base_url', 'desc')->first();
                if ($exist_file != null) {
                    $filePathArray = explode('/', $exist_file->base_url);
                    $fileNameArray = explode('-', $filePathArray[4]);
                    $fileExtensionArray = explode('.', $fileNameArray[2]);
                    $index = $fileExtensionArray[0];
                    $index = $index + 1;
                    $fileName = $order->customer_number . '-' . $order->order_number . '-' . $index . '.' . $file->getClientOriginalExtension();
                    if ($file->storeAs($filePath, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $index;
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
                } else {
                    if ($file->storeAs($filePath, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $key + 1;
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
        }
    }
    public function CustomerOrderFormMail(Request $request)
    {

        $project_name = $request->input('project_name');
        $size = $request->input('size');
        $width_height = $request->input('width_height');
        $products = $request->input('products');
        $special_instructions = $request->input('special_instructions');

        $customer = auth()->user();
        $order = Order::where('project_name', $project_name)
            ->where('size', $size)
            ->where('width_height', $width_height)
            ->where('products', $products)
            ->where('special_instructions', $special_instructions)->first();

        $em_parameter = CustomerEmParameter::where('customer_id', $customer->id)->first();
        $ve_parameter = CustomerVeParameter::where('customer_id', $customer->id)->first();
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $en_em_parameter = $translator->translate($em_parameter);
        $en_order = $translator->translate($order);
        $en_em_parameter = json_decode($en_em_parameter);
        $en_order = json_decode($en_order);


        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        if ($order->type == 'Embroidery') {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '1')->first()->email;
        } else {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '2')->first()->email;
        }
        $recipient_customer = auth()->user()->email;



        $files = [];
        $attachmanet_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
        foreach ($attachmanet_files as $attachmant) {
            $customer_number = explode('/', $attachmant)[1];
            $project_name = explode('/', $attachmant)[2];
            $folder_name = explode('/', $attachmant)[3];
            $filename = explode('/', $attachmant)[4];
            if ($folder_name == 'Originaldatei') {
                $files[] = 'public/' . $customer_number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
            }
        }

        $zip = new ZipArchive();
        $order = Order::findOrFail($order->id);
        $folder = $order->customer_number . '/' . $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/' . $request->get('type') . '/';
        $files = Order_file_upload::where('order_id', $order->id)->where('base_url', 'LIKE', '%' . $folder . '%')->get();
        $fileName = $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '.zip';
        if ($zip->open(storage_path('app/public/' . $folder . $fileName), ZipArchive::CREATE) === true) {
            $files = Storage::allFiles('public/' . $folder);
            foreach ($files as $key => $file) {
                $relativeNameInZipFile = basename('public/' . $folder . $file);
                $filePathArray = explode('/', $file);
                $zip->addFile(storage_path('app/' . $file), $filePathArray[3] . '/' . $relativeNameInZipFile);
            }
            $zip->close();
        }
        $zipStoragePath = 'public/' . $folder . $fileName;



        if ($order && $order->emailed == null) {
            try {
                Mail::to($recipient_admin)->send(new OrderFormMail($order, $customer, $em_parameter, $ve_parameter, $files));
                // Mail::to($recipient_freelancer)->send(new OrderFormFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $zipStoragePath));
                // Mail::to($recipient_customer)->send(new OrderFormCustomerMail($order, $customer, $em_parameter, $ve_parameter, $files));
                Mail::to('habedere@sinzers.de')->send(new OrderFormCustomerMail($order, $customer, $em_parameter, $ve_parameter, $files));
                Mail::to('christoperw818@gmail.com')->send(new OrderFormFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $zipStoragePath));
                Mail::to('info@lioncap.de')->send(new OrderFormFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $zipStoragePath));


                $order->emailed = 'emailed';
                $order->save();
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        }

    }
    public function fileUploadChange(Request $request)
    {
        $order_id = $request->post('order_id');
        $order_change_message = $request->post('order_change_textarea');
        $time = $request->post('time');


        $order = Order::findOrfail($order_id);
        $customer = User::findOrfail($order->user_id);


        OrderChange::where('time', $time)->delete();
        $change_number = OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first() ?
            OrderChange::where('order_id', $order->id)->where('changed_from', 'LIKE', '%' . 'customer' . '%')->orderBy('id', 'desc')->first()->change_number : 0;

        $order_change = new OrderChange();
        $order_change->customer_id = $order->user_id;
        $order_change->customer_name = $customer->name;
        $order_change->order_id = $order_id;
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
                $fileName = $order->customer_number . '-' . $order->order_number . '-' . ($key + 1) . '.' . $file->getClientOriginalExtension();
                $exist_file = Order_file_upload::where('base_url', 'LIKE', 'storage/' . $filePath . $folderName . '%')->orderBy('base_url', 'desc')->first();
                if ($exist_file != null) {
                    $filePathArray = explode('/', $exist_file->base_url);
                    $fileNameArray = explode('-', $filePathArray[4]);
                    $fileExtensionArray = explode('.', $fileNameArray[2]);
                    $index = $fileExtensionArray[0];
                    $index = $index + 1;
                    $fileName = $order->customer_number . '-' . $order->order_number . '-' . $index . '.' . $file->getClientOriginalExtension();
                    if ($file->storeAs($filePath . $folderName, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $index;
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
                } else {
                    if ($file->storeAs($filePath . $folderName, $fileName, 'public')) {
                        $order_file_upload = new Order_file_upload();
                        $order_file_upload->order_id = $order->id;
                        $order_file_upload->index = $key + 1;
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
    }



    public function OrderChange(Request $request)
    {
        $authuser = auth()->user();
        if ($request->ajax()) {
            $change_data = Order_file_upload::where('order_id', $request->id)->orderBy('order_id', 'desc')->get();

            return DataTables::of($change_data)->addIndexColumn()
                ->editColumn('customer_number', function ($row) {
                    $customer_number = $row->order->customer_number;
                    return $customer_number;
                })
                ->editColumn('order_number', function ($row) {
                    $order_number = $row->order->order_number;
                    return $order_number;
                })
                ->addColumn('index', function ($row) {
                    $index = '<span id="index_span' . $row->id . '">' . $row->index . '</span><input style="width: 50px; display: none" id="index_input' . $row->id . '" value="' . $row->index . '">';
                    return $index;
                })
                ->addColumn('edit', function ($row) {

                    $btn = '<button id="button_edit' . $row->id . '" style="border:none;  background-color:#aaa;   border-radius: 3px; padding:2px 8px;" onclick="handleEditClick(' . $row->id . ')">Edit</button><button id="button_save' . $row->id . '" style="border:none;background-color:#aaa;border-radius:3px;padding:2px 8px;display:none" onclick="handleSaveClick(' . $row->id . ')">Save</button>';
                    return $btn;
                })
                ->rawColumns(['customer_number', 'order_number', 'index', 'edit'])
                ->make(true);
        }
    }

    public function OrderChangeText(Request $request)
    {
        $order_id = $request->post('order_id');
        $order_change_message = $request->post('order_change_textarea');
        $time = $request->post('time');


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
        $order_change->emailed = 'emailed';
        $order_change->save();

        $order->status = 'Änderung';
        $order->save();
        return response()->json($order_change->message);
    }
    public function OrderRequestMail(Request $request)
    {
        $order = Order::findOrfail($request->get('order_id'));
        $customer = User::findOrfail($order->user_id);
        $em_parameter = CustomerEmParameter::where('customer_id', $customer->id)->first();
        $ve_parameter = CustomerVeParameter::where('customer_id', $customer->id)->first();
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $en_em_parameter = $translator->translate($em_parameter);
        $en_order = $translator->translate($order);
        $en_em_parameter = json_decode($en_em_parameter);
        $en_order = json_decode($en_order);

        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        if ($order->type == 'Embroidery') {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '1')->first()->email;
        } else {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '2')->first()->email;
        }
        $recipient_customer = $customer->email;
        $sender = $customer->email;

        $files = [];
        $folder_name = [];
        $attachment_files = Order_file_upload::where('order_id', $order->id)->pluck('base_url')->toArray();
        foreach ($attachment_files as $attachmant) {
            $folder_name[] = explode('/', $attachmant)[3];
        }
        $maxFolderNumber = -1;
        foreach ($folder_name as $name) {
            if (strpos($name, 'Änderungsdateien Kunde') === 0) {
                $folderNumber = (int) substr($name, strlen('Änderungsdateien Kunde'));
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
            if (strpos($folder_name, 'Änderungsdateien Kunde') === 0) {
                $folderNumber = (int) substr($folder_name, strlen('Änderungsdateien Kunde'));

                if ($folderNumber == $maxFolderNumber) {
                    $files[] = 'public/' . $customer_Number . '/' . $project_name . '/' . $folder_name . '/' . $filename;
                }
            }
        }
        $zip = new ZipArchive();
        $order = Order::findOrFail($order->id);
        $folder = $order->customer_number . '/' . $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '/' . $folder_name . '/';
        $files = Order_file_upload::where('order_id', $order->id)->where('base_url', 'LIKE', '%' . $folder . '%')->get();
        $fileName = $order->customer_number . '-' . $order->order_number . '-' . $order->project_name . '.zip';
        if ($zip->open(storage_path('app/public/' . $folder . $fileName), ZipArchive::CREATE) === true) {
            $files = Storage::allFiles('public/' . $folder);
            foreach ($files as $key => $file) {
                $relativeNameInZipFile = basename('public/' . $folder . $file);
                $filePathArray = explode('/', $file);
                $zip->addFile(storage_path('app/' . $file), $filePathArray[3] . '/' . $relativeNameInZipFile);
            }
            $zip->close();
        }
        $zipStoragePath = 'public/' . $folder . $fileName;
        $order_change1 = OrderChange::where('order_id', $order->id)->where('change_number', 1)->first();
        $order_change2 = OrderChange::where('order_id', $order->id)->where('change_number', 2)->first();
        $order_change3 = OrderChange::where('order_id', $order->id)->where('change_number', 3)->first();
        $order_change4 = OrderChange::where('order_id', $order->id)->where('change_number', 4)->first();
        if ($order_change1 && $order_change1->emailed == null) {
            $text = $order_change1->message;
            try {
                Mail::to($recipient_admin)->send(new OrderRequestAdmin($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                // Mail::to($recipient_freelancer)->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                // Mail::to($recipient_customer)->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                Mail::to('christoperw818@gmail.com')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('info@lioncap.de')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('habedere@sinzers.de')->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                $order_change1->emailed = 'emailed';
                $order_change1->save();
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        } else if ($order_change2 && $order_change2->emailed == null) {
            $text = $order_change2->message;
            try {
                Mail::to($recipient_admin)->send(new OrderRequestAdmin($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                // Mail::to($recipient_freelancer)->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                // Mail::to($recipient_customer)->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                Mail::to('christoperw818@gmail.com')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('info@lioncap.de')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('habedere@sinzers.de')->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                $order_change2->emailed = 'emailed';
                $order_change2->save();
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        } else if ($order_change3 && $order_change3->emailed == null) {
            $text = $order_change3->message;
            try {
                Mail::to($recipient_admin)->send(new OrderRequestAdmin($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                // Mail::to($recipient_freelancer)->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                // Mail::to($recipient_customer)->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                Mail::to('christoperw818@gmail.com')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('info@lioncap.de')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('habedere@sinzers.de')->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                $order_change3->emailed = 'emailed';
                $order_change3->save();
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        } else if ($order_change4 && $order_change4->emailed == null) {
            $text = $order_change4->message;
            try {
                Mail::to($recipient_admin)->send(new OrderRequestAdmin($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                // Mail::to($recipient_freelancer)->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                // Mail::to($recipient_customer)->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                Mail::to('christoperw818@gmail.com')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('info@lioncap.de')->send(new OrderRequestFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text, $zipStoragePath));
                Mail::to('habedere@sinzers.de')->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
                $order_change4->emailed = 'emailed';
                $order_change4->save();
                return response()->json(['message' => 'Great! Successfully sent your email']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return response()->json(['error' => 'Sorry! Please try again later']);
            }
        }

    }
    public function OrderRequestTextMail(Request $request)
    {
        $order = Order::findOrfail($request->post('order_id'));
        $text = $request->post('text');
        $customer = User::findOrfail($order->user_id);
        $em_parameter = CustomerEmParameter::where('customer_id', $customer->id)->first();
        $ve_parameter = CustomerVeParameter::where('customer_id', $customer->id)->first();
        $translator = new GoogleTranslate();
        $translator->setSource('de');
        $translator->setTarget('en');
        $en_em_parameter = $translator->translate($em_parameter);
        $en_order = $translator->translate($order);
        $en_em_parameter = json_decode($en_em_parameter);
        $en_order = json_decode($en_order);

        $recipient_admin = User::where('user_type', 'admin')->first()->email;
        if ($order->type == 'Embroidery') {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '1')->first()->email;
        } else {
            $recipient_freelancer = User::where('user_type', 'freelancer')->where('category_id', '2')->first()->email;
        }
        $recipient_customer = $customer->email;

        $files = [];

        try {
            Mail::to($recipient_admin)->send(new OrderRequestAdmin($order, $customer, $em_parameter, $ve_parameter, $text, $files));
            // Mail::to($recipient_freelancer)->send(new OrderrRequestTextFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text));
            // Mail::to($recipient_customer)->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
            Mail::to('christoperw818@gmail.com')->send(new OrderrRequestTextFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text));
            Mail::to('info@lioncap.de')->send(new OrderrRequestTextFreelancerMail($order, $en_order, $customer, $em_parameter, $en_em_parameter, $ve_parameter, $text));
            Mail::to('habedere@sinzers.de')->send(new OrderRequestCustomer($order, $customer, $em_parameter, $ve_parameter, $text, $files));
            return response()->json(['message' => 'Great! Successfully sent your email']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Sorry! Please try again later']);
        }
    }

    public function importData(Request $request)
    {
        $uploads = $request->file();
        $uploadDir = 'public/';
        // Split the string containing the list of file paths into an array
        $paths = explode("###", rtrim($request->post('paths'), "###"));
        // sort($uploads);
        // sort($paths);
        // Loop through files sent
        $sub_folder = '';
        $order_id = 1;
        foreach ($uploads as $key => $current) {
            // Stores full destination path of file on server
            $uploadFile = $uploadDir . rtrim($paths[$key], "/.");
            // Stores containing folder path to check if dir later
            $folder = substr($uploadFile, 0, strrpos($uploadFile, "/"));

            $folders = explode('/', $paths[$key]);
            $customer_number = $folders[0];
            $second_folder = $folders[1];
            $second_folder_array = explode('-', $second_folder);
            $order_number = $second_folder_array[1];
            $project_name = $second_folder_array[2];
            $ordered_from = 'Mustermann';
            $status = "Ausgeliefert";
            $type = 'Embroidery';
            $size = "24";
            $width_height = "Höhe";
            $deliver_time = "STANDARD";
            $products = "Patch, Patch Material";
            $special_instructions = "Es handelt sich um spezielle Anweisungen";
            $category_id = 1;
            $user_id = auth()->user()->id;
            $assigned_to = 4;
            $org_id = auth()->user()->id;

            $last_folder = $second_folder;

            // Check whether the current entity is an actual file or a folder (With a . for a name)
            if (strlen($current->getClientOriginalName()) != 1) {
                Storage::makeDirectory($folder);
                if ($sub_folder != $last_folder) {
                    $order = new Order();
                    $order->customer_number = $customer_number;
                    $order->order_number = $order_number;
                    $order->project_name = $project_name;
                    $order->ordered_from = $ordered_from;
                    $order->status = $status;
                    $order->type = $type;
                    $order->size = $size;
                    $order->deliver_time = $deliver_time;
                    $order->width_height = $width_height;
                    $order->products = $products;
                    $order->special_instructions = $special_instructions;
                    $order->category_id = $category_id;
                    $order->user_id = $user_id;
                    $order->assigned_to = $assigned_to;
                    $order->org_id = $org_id;
                    $order->save();
                    $order_id = $order->id;
                }
                $sub_folder = $last_folder;
                // Moves current file to upload destination
                if ($current->storePubliclyAs($folder, $current->getClientOriginalName())) {
                    $order_file_upload = new Order_file_upload();
                    $order_file_upload->order_id = $order_id;
                    $order_file_upload->index = 1;
                    $order_file_upload->extension = $current->getClientOriginalExtension();
                    $order_file_upload->base_url = 'storage/' . $paths[$key];
                    $order_file_upload->save();
                    echo "The file " . $current->getClientOriginalName() . " has been uploaded";
                } else
                    echo "Error";
            }

        }
    }
    public function display_embDetails($local, $id)
    {

        $authuser = auth()->user()->id;
        $dataFiles = Order::with('Orderfile_uploads')->where('id', $id)->first();
        $file_string = $dataFiles->Orderfile_uploads->file_upload;

        $uploadFiles = $array = json_decode($file_string, true);

        $ordersDetails = Order::with(['category', 'Order_address', 'Orderfile_formats', 'Orderfile_uploads'])->where('id', $id)->get();

        return view('users.orders.update_embOrder', compact('ordersDetails', 'uploadFiles'));
    }

    public function display_vectorDetails($local, $id)
    {
        $authuser = auth()->user()->id;
        $dataFiles = Order::with('Orderfile_uploads')->where('id', $id)->first();

        $file_string = $dataFiles->Orderfile_uploads->file_upload;

        $uploadFiles = $array = json_decode($file_string, true);

        $ordersDetails = Order::with(['category', 'Order_address', 'Orderfile_formats'])->where('id', $id)->get();

        return view('users.orders.update_vectorOrder', compact('ordersDetails', 'uploadFiles'));
    }


    public function updated_embroidery(Request $request)
    {

        try {
            $id = $request->id;
            $data = $request->all();
            $users = Auth::user()->id;

            // $check_status = Order::where('id',$id)->first();
            $val_add = Order_address::where('order_id', $id)->first();

            // send mail to admin and freelance
            $freelancer = User::where('category_id', '1')->first();
            $data['order_id'] = $id;
            $data['user'] = Auth::user();
            Mail::to([env('ADMIN_EMAIL'), $freelancer->email])->send(new OrderUpdateEmail($data));
            if (isset($val_add)) {
                $old_addFile = public_path('images/customer/embroidery/businessRegisterFiles/' . $val_add->address_file);

                if ($request->hasFile('address_file')) {

                    $file = $request->file('address_file');

                    // Check if a file is present in the request
                    if ($file) {
                        $file = $request->file('address_file');
                        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $finalName = $users . '-' . $id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();
                        $destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
                        $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $imageUrl = Storage::disk('s3')->url($destination);
                        $final_file = $imageUrl;
                    } else {
                        // If no new file is uploaded, retain the existing file name
                        $final_file = $val_add->address_file;
                    }
                } else {
                    // If no new file is uploaded, retain the existing file name
                    $final_file = $val_add->address_file;
                }
            } else {
                $file = $request->file('address_file');

                // Check if a file is present in the request
                if ($file) {
                    $file = $request->file('address_file');
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $finalName = $users . '-' . $id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();
                    $destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
                    $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                        'ContentDisposition' => 'attachment'
                    ]);
                    $imageUrl = Storage::disk('s3')->url($destination);
                    $final_file = $imageUrl;
                } else {
                    $final_file = null; // or set it to a default file name if needed
                }
            }

            $order = Order::where('id', $id)->update([

                'project_name' => $data['project_name'],
                'category_id' => '1',
                'user_id' => $users,
                'delivery_time' => $data['delivery_time'],
                'end_product' => $data['end_product'],
                'selection' => $data['selection'],
                'emb_info' => $data['emb_info'],
                'emb_size' => $data['emb_size'],
                'instructions' => $data['instructions'],
                'status' => 'pending',

            ]);
            Order_address::where('order_id', $id)->update([
                'Salutation' => $data['Salutation'],
                'company' => $data['company'],
                'full_name' => $data['full_name'],
                'address' => $data['address'],
                'zip_code' => $data['zip_code'],
                'place' => $data['place'],
                'vat_no' => $data['vat_no'],
                'contact_no' => $data['contact_no'],
                'email' => $data['email'],
                'site' => $data['site'],
                'address_file' => $final_file,
            ]);

            Order_file_format::where('order_id', $id)->update([
                'file_format' => $data['file_format'],
                'view_file_format' => $data['view_file_format']
            ]);


            $val_upload = Order_file_upload::where('order_id', $id)->first();

            if (isset($val_upload)) {

                $PrevFiles = json_decode($val_upload->file_upload, true);
                if ($request->hasFile('file_upload')) {
                    $files = [];
                    foreach ($request->file('file_upload') as $file) {
                        $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $uploadFinalName = $users . '-' . $id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                        $upload_destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                        $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                        $files[] = $upload_imageUrl;
                    }
                    $mergedFiles = array_merge($PrevFiles, $files);
                    Order_file_upload::where('order_id', $id)->update([
                        'file_upload' => json_encode($mergedFiles)
                    ]);
                }
            } else {

                if ($request->hasFile('file_upload')) {
                    $files = [];
                    foreach ($request->file('file_upload') as $file) {
                        $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $uploadFinalName = $users . '-' . $id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                        $upload_destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                        $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                        $files[] = $upload_imageUrl;
                    }
                }

                $orderfile = new Order_file_upload();
                $orderfile->order_id = $id;
                $orderfile->file_upload = json_encode($files);
                $orderfile->save();
            }
            return response()->json(['message' => 'Record updated successfully']);
        } catch (ModelNotFoundException $e) {
            // If the record is not found, handle the exception here
            return response()->json(['message' => 'Record not found' . $e->getMessage()], 404);
        } catch (\Exception $e) {
            // Handle any other unexpected exceptions here
            return response()->json(['message' => 'Something went wrong' . $e->getMessage()], 500);
        }
    }



    public function vectordeleteFile(Request $request)
    {

        $index = $request->input('index');
        $id = $request->input('id');

        // Retrieve the files for the given order_id
        $files = Order_file_upload::where('order_id', $id)->first();

        if ($files) {
            $filearray = json_decode($files->file_upload, true);

            if (isset($filearray[$index])) {
                unset($filearray[$index]);
                // Update the model with the modified file array
                $files->file_upload = json_encode(array_values($filearray));
                $files->save();

                return response()->json(['message' => 'File deleted successfully']);
            } else {
                return response()->json(['message' => 'File not found']);
            }
        } else {
            return response()->json(['message' => 'Order files not found']);
        }
    }

    public function embdeleteFile(Request $request)
    {
        $index = $request->input('index');
        $id = $request->input('id');
        // Retrieve the files for the given order_id
        $files = Order_file_upload::where('order_id', $id)->first();

        if ($files) {
            $filearray = json_decode($files->file_upload, true);

            if (isset($filearray[$index])) {

                // Remove the deleted file from the files array
                unset($filearray[$index]);

                // Update the model with the modified file array
                $files->file_upload = json_encode(array_values($filearray));
                $files->save();
            } else {
                return response()->json(['message' => 'File not found']);
            }
        } else {
            return response()->json(['message' => 'Order files not found']);
        }
    }


    public function updated_vector(Request $request)
    {


        try {
            $id = $request->id;
            $data = $request->all();

            $users = Auth::user()->id;

            // send mail to admin and freelance
            $freelancer = User::where('category_id', '2')->first();
            $data['order_id'] = $id;
            $data['user'] = Auth::user();
            Mail::to([env('ADMIN_EMAIL'), $freelancer->email])->send(new OrderUpdateEmail($data));

            $val_add = Order_address::where('order_id', $id)->first();

            if (isset($val_add)) {
                $old_addFile = public_path('images/customer/vector/businessRegisterFiles/' . $val_add->address_file);

                if ($request->hasFile('address_file')) {


                    $file = $request->file('address_file');

                    // Check if a file is present in the request
                    if ($file) {
                        $file = $request->file('address_file');
                        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $finalName = $users . '-' . $id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();
                        $destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
                        $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $imageUrl = Storage::disk('s3')->url($destination);
                        $final_file = $imageUrl;
                    } else {
                        // If no new file is uploaded, retain the existing file name
                        $final_file = $val_add->address_file;
                    }
                } else {
                    // If no new file is uploaded, retain the existing file name
                    $final_file = $val_add->address_file;
                }
            } else {
                $file = $request->file('address_file');

                if ($file) {
                    $file = $request->file('address_file');
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $finalName = $users . '-' . $id . '-' . Helper::slugify($fileName) . '.' . $file->getClientOriginalExtension();
                    $destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $finalName;
                    $path = Storage::disk('s3')->put($destination, file_get_contents($file), [
                        'ContentDisposition' => 'attachment'
                    ]);
                    $imageUrl = Storage::disk('s3')->url($destination);
                    $final_file = $imageUrl;
                } else {
                    $final_file = null; // or set it to a default file name if needed
                }
            }

            Order_address::where('order_id', $id)->update([
                'Salutation' => $data['Salutation'],
                'company' => $data['company'],
                'full_name' => $data['full_name'],
                'address' => $data['address'],
                'zip_code' => $data['zip_code'],
                'place' => $data['place'],
                'vat_no' => $data['vat_no'],
                'contact_no' => $data['contact_no'],
                'email' => $data['email'],
                'site' => $data['site'],
                'address_file' => $final_file,
            ]);

            $order = Order::where('id', $id)->update([
                'project_name' => $data['project_name'],
                'category_id' => '2',
                'user_id' => $users,
                'delivery_time' => $data['delivery_time'],
                'selection' => $data['selection'],
                'instructions' => $data['instructions'],
                'status' => 'pending',
            ]);

            Order_file_format::where('order_id', $id)->update([
                'file_format' => $data['file_format'],
                'view_file_format' => $data['view_file_format']
            ]);



            $val = Order_file_upload::where('order_id', $id)->first();
            if (isset($val)) {
                $PrevFiles = json_decode($val->file_upload, true);

                if ($request->File('file_upload')) {

                    $files = [];
                    foreach ($request->file('file_upload') as $file) {
                        $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $uploadFinalName = $users . '-' . $id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                        $upload_destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                        $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                        $files[] = $upload_imageUrl;
                    }
                    $mergedFiles = array_merge($PrevFiles, $files);

                    Order_file_upload::where('order_id', $id)->update([
                        'file_upload' => json_encode($mergedFiles)
                    ]);
                }
            } else {
                if ($request->hasFile('file_upload')) {
                    $files = [];
                    foreach ($request->file('selectedfiles') as $file) {
                        $uploadFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $uploadFinalName = $users . '-' . $id . '-' . Helper::slugify($uploadFileName) . '.' . $file->getClientOriginalExtension();
                        $upload_destination = $users . '-' . $id . '-' . Helper::slugify($data['project_name']) . '/Originaldatei/' . $uploadFinalName;
                        $upload_path = Storage::disk('s3')->put($upload_destination, file_get_contents($file), [
                            'ContentDisposition' => 'attachment'
                        ]);
                        $upload_imageUrl = Storage::disk('s3')->url($upload_destination);
                        $files[] = $upload_imageUrl;
                    }
                }

                $orderfile = new Order_file_upload();
                $orderfile->order_id = $id;
                $orderfile->file_upload = json_encode($files);
                $orderfile->save();
            }

            return response()->json(['message' => 'Record updated successfully']);
        } catch (ModelNotFoundException $e) {

            return response()->json(['message' => 'Record not found' . $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong' . $e->getMessage()], 500);
        }
    }
    public function orderDetailParameter(Request $request)
    {
        $order = Order::findOrfail($request->get("id"));
        $em_parameter = CustomerEmParameter::where('customer_id', $order->user_id)->first();
        $ve_parameter = CustomerVeParameter::where('customer_id', $order->user_id)->first();
        return response()->json([$em_parameter, $ve_parameter]);
    }
}
