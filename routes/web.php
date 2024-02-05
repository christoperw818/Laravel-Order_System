<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\userController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FreelancerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeliveryFileController;
use App\Http\Middleware\LoginMiddleware;
use App\Http\Middleware\RoleMiddleware;




Route::get('/team', [FreelancerController::class, 'goFreelancerLogin'])->name('team');
Route::get('/admin', [AdminController::class, 'goAdminLogin'])->name('admin');
Route::get('/{locale?}', [CustomerController::class, 'homePage'])->name('homepage');
Route::post('/import-data', [OrderController::class, 'importData'])->name('import-data');
Route::post('/upload', [OrderController::class, 'fileUpload'])->name('upload');
Route::post('/employer-upload', [OrderController::class, 'EmployerfileUpload'])->name('employer-upload');
Route::post('/upload-change', [OrderController::class, 'fileUploadChange'])->name('upload-change');
Route::post('/freelancer-job-upload', [FreelancerController::class, 'JobFileUpload'])->name('freelancer-job-upload');
Route::post('/embroidery-upload', [FreelancerController::class, 'EmbroideryFileUpload'])->name('embroidery-upload');
Route::post('/vector-upload', [FreelancerController::class, 'VectorFileUpload'])->name('vector-upload');
Route::post('/admin-upload', [AdminController::class, 'adminFileUpload'])->name('admin-upload');
Route::post('/admin-job-upload', [AdminController::class, 'JobFileUpload'])->name('admin-job-upload');
Route::post('/admin-change-upload', [AdminController::class, 'ChangeFileUpload'])->name('admin-change-upload');
Route::post('/admin-request-upload', [AdminController::class, 'RequestFileUpload'])->name('admin-request-upload');
Route::get('/multi-download/{id}', [OrderController::class, 'multiple'])->name('multi-download');
Route::post('/slack/event', [AdminController::class, 'adminReceiveChat']);
Route::post('/slack/embroidery/freelancer/event', [FreelancerController::class, 'emRecieveSlackMessage']);
Route::post('/slack/vector/freelancer/event', [FreelancerController::class, 'veRecieveSlackMessage']);
Route::post('/slack/customer/event', [CustomerController::class, 'customerRecieveSlackMessage']);


// admin Route
Route::middleware([LoginMiddleware::class . ':admin'])->prefix('{locale}/admin')->group(function () {
    Route::get('/login', [AdminController::class, 'adminloginpage'])->name('admin-login');
    Route::get('/signin', [AdminController::class, 'adminLogin'])->name('admin-signin');
});
Route::middleware([RoleMiddleware::class . ':admin'])->prefix('{locale}/admin')->group(function () {
    Route::get('/logout', [AdminController::class, 'adminLogout'])->name('admin-logout');
    Route::get('/change-avatar', [AdminController::class, 'adminChangEAvatar'])->name('admin-change-avatar');
    Route::post('/change-avatar-handle', [AdminController::class, 'adminChangEAvatarHandle'])->name('admin-change-avatar-handle');
    Route::get('/admin-view-orders', [AdminController::class, 'AdminviewOrders']);
    Route::get('/order-details/{id}', [AdminController::class, 'orderDetails']);
    Route::get('/change-password', [AdminController::class, 'changePassword']);
    Route::post('/update-password', [AdminController::class, 'updatePassword']);
    Route::get('/profile', [AdminController::class, 'adminProfile']);
    Route::get('/get-differences/{id}', [AdminController::class, 'getDifferences']);
    Route::post('/accept-change', [AdminController::class, 'acceptChangeRequest']);
    Route::get('/accept-change-mail', [AdminController::class, 'AcceptProfileChangeMail']);
    Route::post('/decline-change/{id}', [AdminController::class, 'declineChangeRequest']);
    Route::get('/decline-change-mail', [AdminController::class, 'DeclineProfileChangeMail']);
    Route::get('/customer-list', [AdminController::class, 'CustomerList'])->name('admin-customer-list');
    Route::get('/customer-search', [AdminController::class, 'CustomerList'])->name('admin-customer-search');
    Route::get('/get-customer-profile', [AdminController::class, 'GetCustomerProfile'])->name('admin-get-customer-profile');
    Route::post('/change-profile', [AdminController::class, 'ChangeProfile'])->name('admin-change-profile');
    Route::post('/add-customer', [AdminController::class, 'AddCustomer'])->name('admin-add-customer');
    Route::get('/customer-search-table', [AdminController::class, 'CustomerSearchTable'])->name('admin-customer-search-table');
    Route::get('/customer-searched-result', [AdminController::class, 'CustomerSearchResult'])->name('admin-customer-searched-result');
    Route::post('/confirm-profile', [AdminController::class, 'ConfirmProfile'])->name('admin-confirm-profile');
    Route::get('/confirm-profile-mail', [AdminController::class, 'ConfirmProfileMail'])->name('admin-confirm-profile-mail');
    Route::post('/decline-profile', [AdminController::class, 'DeclineProfile'])->name('admin-decline-profile');
    Route::get('/decline-profile-mail', [AdminController::class, 'DeclineProfileMail'])->name('admin-decline-profile-mail');
    Route::get('/parameter-em-table', [AdminController::class, 'EmParameterTable'])->name('admin-parameter-em-table');
    Route::get('/parameter-ve-table', [AdminController::class, 'VeParameterTable'])->name('admin-parameter-ve-table');
    Route::get('/parameter-em', [AdminController::class, 'EmParameter'])->name('admin-parameter-em');
    Route::get('/parameter-ve', [AdminController::class, 'VeParameter'])->name('admin-parameter-ve');
    Route::get('/green-table', [AdminController::class, 'AdminGreenTable'])->name('admin-green-table');
    Route::get('/yellow-table', [AdminController::class, 'AdminYellowTable'])->name('admin-yellow-table');
    Route::get('/red-table', [AdminController::class, 'AdminRedTable'])->name('admin-red-table');
    Route::get('/blue-table', [AdminController::class, 'AdminBlueTable'])->name('admin-blue-table');
    Route::get('/all-table', [AdminController::class, 'AdminAllTable'])->name('admin-all-table');
    Route::get('/get-order-detail', [FreelancerController::class, 'FreelancergetOrderDetail'])->name('admin-get-order-detail');
    Route::get('/order-detail', [AdminController::class, 'AdminOrderDetail'])->name('admin-order-detail');
    Route::get('/parameter', [AdminController::class, 'Parameter'])->name('admin-parameter');
    Route::get('/startjob', [FreelancerController::class, 'StartJob'])->name('admin-startjob');
    Route::get('/endjob', [FreelancerController::class, 'EndJob'])->name('admin-endjob');
    Route::get('/get-request-detail', [FreelancerController::class, 'getRequestDetail'])->name('admin-get-request-detail');
    Route::get('/change-parameter', [AdminController::class, 'Parameter'])->name('admin-change-parameter');
    Route::get('/change-order-detail', [AdminController::class, 'AdminChangeOrderDetail'])->name('admin-change-order-detail');
    Route::get('/endchange', [AdminController::class, 'EndChange'])->name('admin-endchange');
    Route::post('/delete-order', [AdminController::class, 'DeleteOrder'])->name('admin-delete-order');
    Route::post('/request-text', [AdminController::class, 'RequestText'])->name('admin-request-text');
    Route::get('/detail-delete-file/{id}', [FreelancerController::class, 'DeleteFile']);
    Route::get('/change_delete_file/{id}', [FreelancerController::class, 'DeleteFile']);
    Route::post('/change-em-parameter-confirm', [AdminController::class, 'EmParameterConfirm'])->name('admin-change-em-parameter-confirm');
    Route::post('/change-ve-parameter-confirm', [AdminController::class, 'VeParameterConfirm'])->name('admin-change-ve-parameter-confirm');
    Route::get('/change-em-parameter-confirm-mail', [AdminController::class, 'EmParameterConfirmMail'])->name('admin-change-em-parameter-confirm-mail');
    Route::get('/change-ve-parameter-confirm-mail', [AdminController::class, 'VeParameterConfirmMail'])->name('admin-change-ve-parameter-confirm-mail');
    Route::post('/change-em-parameter-decline', [AdminController::class, 'EmParameterDecline'])->name('admin-change-em-parameter-decline');
    Route::post('/change-ve-parameter-decline', [AdminController::class, 'VeParameterDecline'])->name('admin-change-ve-parameter-decline');
    Route::get('/change-em-parameter-decline-mail', [AdminController::class, 'EmParameterDeclineMail'])->name('admin-change-em-parameter-decline-mail');
    Route::get('/change-ve-parameter-decline-mail', [AdminController::class, 'VeParameterDeclineMail'])->name('admin-change-ve-parameter-decline-mail');
    Route::post('/change-em-parameter-change', [AdminController::class, 'EmParameterChange'])->name('admin-change-em-parameter-change');
    Route::post('/change-ve-parameter-change', [AdminController::class, 'VeParameterChange'])->name('admin-change-ve-parameter-change');
    Route::get('/dashboard-green-table', [AdminController::class, 'DashboardGreenTable'])->name('admin-dashboard-green-table');
    Route::get('/dashboard-red-table', [AdminController::class, 'DashboardRedTable'])->name('admin-dashboard-red-table');
    Route::get('/dashboard-yellow-table', [AdminController::class, 'DashboardYellowTable'])->name('admin-dashboard-yellow-table');
    Route::get('/dashboard-blue-table', [AdminController::class, 'DashboardBlueTable'])->name('admin-dashboard-blue-table');
    Route::get('/em-payment', [AdminController::class, 'EmPayment'])->name('admin-em-payment');
    Route::get('/em-payment-sum', [AdminController::class, 'EmPaymentSum'])->name('admin-em-payment-sum');
    Route::post('/em-payment-handle', [AdminController::class, 'EmPaymentHandle'])->name('admin-em-payment-handle');
    Route::get('/ve-payment', [AdminController::class, 'VePayment'])->name('admin-ve-payment');
    Route::get('/ve-payment-sum', [AdminController::class, 'VePaymentSum'])->name('admin-ve-payment-sum');
    Route::post('/ve-payment-handle', [AdminController::class, 'VePaymentHandle'])->name('admin-ve-payment-handle');
    Route::post('/order-count', [AdminController::class, 'OrderCount'])->name('admin-order-count');
    Route::get('/embroidery-payment-archive', [AdminController::class, 'EmbroideryPaymentArchive'])->name('admin-embroidery-payment-archive');
    Route::get('/vector-payment-archive', [AdminController::class, 'VectorPaymentArchive'])->name('admin-vector-payment-archive');
    Route::post('/change-customer-avatar', [AdminController::class, 'ChangeAvatar'])->name('admin-change-customer-avatar');
    Route::post('/delete-customer', [AdminController::class, 'deleteCustomer'])->name('admin-delete-customer');
    Route::get('/chat-get', [AdminController::class, 'adminChatGet'])->name('admin-chat-get');
    Route::get('/chat-long-polling', [AdminController::class, 'adminChatLongPolling']);
    Route::post('/chat', [AdminController::class, 'adminChat'])->name('admin-chat');
});

//customer route
Route::middleware([LoginMiddleware::class . ':customer'])->prefix('{locale}/customer')->group(function () {
    Route::get('/login', [CustomerController::class, 'login'])->name('customer-login');
    Route::post('/customer-login', [CustomerController::class, 'customerLogin']);
    Route::get('/registration', [CustomerController::class, 'registration']);
    Route::post('/customer-registration', [CustomerController::class, 'customerRegistration']);
    Route::get('/setpassword/{id}', [CustomerController::class, 'setEmployerPassword']);
    Route::post('/employer-password-update', [CustomerController::class, 'EmployerPasswordUpdate']);
});

Route::middleware([RoleMiddleware::class . ':customer'])->prefix('{locale}/customer')->group(function () {
    Route::get('/logout', [CustomerController::class, 'customerLogout']);
    Route::get('/change-avatar', [CustomerController::class, 'customerChangEAvatar'])->name('customer-change-avatar');
    Route::post('/change-avatar-handle', [CustomerController::class, 'customerChangEAvatarHandle'])->name('customer-change-avatar-handle');
    Route::get('/view-orders', [OrderController::class, 'viewOrder'])->name('customer-vieworders');
    Route::get('/order_detail', [OrderController::class, 'OrderDetail'])->name('customer-order_detail');
    Route::get('/req-order_detail', [OrderController::class, 'OrderDetail'])->name('req-customer-order_detail');
    Route::get('/order_change', [OrderController::class, 'OrderChange'])->name('customer-order_change');
    Route::post('/order-change-text', [OrderController::class, 'OrderChangeText'])->name('customer-order-change-text');
    Route::get('/order-request-mail', [OrderController::class, 'OrderRequestMail'])->name('customer-order-request-mail');
    Route::post('/order-request-text-mail', [OrderController::class, 'OrderRequestTextMail'])->name('customer-order-request-text-mail');
    Route::get('/order_request/{id}', [OrderController::class, 'OrderRequest'])->name('customer-order_request');
    Route::post('/order_delete', [OrderController::class, 'DeleteOrder'])->name('customer-order_delete');
    Route::post('/toggle-status', [OrderController::class, 'toggle_status'])->name('customer-toggle-status');
    // Route::get('/order-details/{id}', [OrderController::class, 'orderDetails']);
    Route::get('/get-order-detail', [OrderController::class, 'getOrderDetail'])->name('customer-get-order-detail');
    Route::get('/req-get-order-detail', [OrderController::class, 'getOrderDetail'])->name('req-customer-get-order-detail');
    Route::post('/order-file-index-change', [OrderController::class, 'changeOrderIndex'])->name('customer-order-file-index-change');
    Route::post('/orderfile-information', [OrderController::class, 'getOrderFileInformation'])->name('customer-orderfile-information');
    Route::get('/dashboard-green-table', [OrderController::class, 'DashboardGreenTable'])->name('customer-dashboard-green-table');
    Route::get('/dashboard-red-table', [OrderController::class, 'DashboardRedTable'])->name('customer-dashboard-red-table');
    Route::get('/dashboard-yellow-table', [OrderController::class, 'DashboardYellowTable'])->name('customer-dashboard-yellow-table');
    Route::get('/dashboard-blue-table', [OrderController::class, 'DashboardBlueTable'])->name('customer-dashboard-blue-table');
    Route::get('/order-detail-parameter', [OrderController::class, 'orderDetailParameter']);

    Route::get('/profile', [CustomerController::class, 'CustomerProfile']);
    Route::post('/profile-update', [CustomerController::class, 'profileUpdate']);
    Route::get('/profile-update-mail', [CustomerController::class, 'profileUpdateMail']);
    Route::get('/get-profile', [CustomerController::class, 'GetProfile']);


    Route::get('/embroidery-information', [OrderController::class, 'EmbroideryInformation'])->name('embroidery-information')->withoutMiddleware([RoleMiddleware::class . ':customer']);
    Route::get('/embroidery-price', [OrderController::class, 'EmbroideryPrice'])->name('embroidery-price')->withoutMiddleware([RoleMiddleware::class . ':customer']);
    Route::get('/vector-information', [OrderController::class, 'VectorInformation'])->name('vector-information')->withoutMiddleware([RoleMiddleware::class . ':customer']);
    Route::get('/vector-price', [OrderController::class, 'VectorPrice'])->name('vector-price')->withoutMiddleware([RoleMiddleware::class . ':customer']);

    Route::post('embroidery-order/save', [OrderController::class, 'embroideryOrderSave'])->name('embroidery-order/save');
    Route::post('embroidery-order/submit', [OrderController::class, 'embroideryOrderSumit'])->name('embroidery-order/submit');

    Route::get("embroidery-order/delete/{id}", [OrderController::class, 'embroidery_orderDelete'])->name('embroidery-order/delete/{id}');
    Route::get("embroidery-order/edit/{id}", [OrderController::class, 'display_embDetails'])->name('embroidery-order/edit/{id}');
    Route::post("updated-embroidery-order", [OrderController::class, 'updated_embroidery']);

    Route::get('/change-password', [CustomerController::class, 'changePassword']);
    Route::POST('/update-password', [CustomerController::class, 'updatePassword'])->name('update-password');

    Route::post('/vector-order/submit', [OrderController::class, 'vectorOrderSumit'])->name('vector-order-submit');

    Route::get("vector-order/delete/{id}", [OrderController::class, 'vector_orderDelete'])->name('vector-order/delete/{id}');
    Route::get("vector-order/edit/{id}", [OrderController::class, 'display_vectorDetails'])->name('vector-order/edit/{id}');
    Route::post("updated-vector-order", [OrderController::class, 'updated_vector'])->name('updated-vector-order');

    Route::post('/vec-delete-file', [OrderController::class, 'vectordeleteFile'])->name('vec-delete-file');
    Route::post('/emb-delete-file', [OrderController::class, 'embdeleteFile'])->name('emb-delete-file');

    Route::get('/files/{id}', [CustomerController::class, 'files']);
    Route::get('invite-employee', [CustomerController::class, 'InviteEmployeeView'])->name('invite-employee');
    Route::post('/send-invite', [CustomerController::class, 'sendInvite'])->name('send-invite');
    Route::get('/listEmployees', [CustomerController::class, 'listEmployees']);
    Route::Post('/add-employee', [CustomerController::class, 'addEmployee'])->name('employer-addemployee');
    Route::get('/get-employee/{id}', [CustomerController::class, 'getEmployee'])->name('employer-getemployee');
    Route::get('/edit-employee/{id}', [CustomerController::class, 'editEmployee']);
    Route::Post('/update-employee', [CustomerController::class, 'updateEmployee']);
    Route::delete('/deleteemployee/{id}', [CustomerController::class, 'deleteEmployee']);
    Route::get('/employee-profile', [CustomerController::class, 'EmployeeProfile']);
    Route::get('/employee-staffs-table', [CustomerController::class, 'EmployeeTable'])->name('employee-staffs-table');

    Route::get('/order-form-mail', [OrderController::class, 'CustomerOrderFormMail'])->name('customer-order-form-mail');
    Route::get('/get-em-parameter', [CustomerController::class, 'GetEmParameter'])->name('customer-get-em-parameter');
    Route::get('/get-ve-parameter', [CustomerController::class, 'GetVeParameter'])->name('customer-get-ve-parameter');
    Route::post('/em-parameter-change', [CustomerController::class, 'ChangeEmParameter'])->name('customer-em-parameter-change');
    Route::get('/em-parameter-change-mail', [CustomerController::class, 'ChangeEmParameterMail'])->name('customer-em-parameter-change-mail');
    Route::post('/ve-parameter-change', [CustomerController::class, 'ChangeVeParameter'])->name('customer-ve-parameter-change');
    Route::get('/ve-parameter-change-mail', [CustomerController::class, 'ChangeVeParameterMail'])->name('customer-ve-parameter-change-mail');
    Route::post('/chat', [CustomerController::class, 'customerChat'])->name('customer-chat');
    Route::get('/chat-get', [CustomerController::class, 'customerChatGet'])->name('customer-chat-get');
    Route::get('/chat-long-polling', [CustomerController::class, 'customerChatLongPolling']);

});


// freelancer route
Route::middleware([LoginMiddleware::class . ':freelancer'])->prefix('{locale}/freelancer')->group(function () {
    Route::get('/login', [FreelancerController::class, 'freelancerloginpage'])->name('freelancer-login');
    Route::post('/signin', [FreelancerController::class, 'freelancerLogin'])->name('freelancer-signin');
});

Route::middleware([RoleMiddleware::class . ':freelancer'])->prefix('{locale}/freelancer')->group(function () {
    Route::get('/logout', [FreelancerController::class, 'freelancerLogout'])->name('freelancer-logout');
    Route::get('/em-change-avatar', [FreelancerController::class, 'EmChangEAvatar'])->name('freelancer-em-change-avatar');
    Route::post('/em-change-avatar-handle', [FreelancerController::class, 'EmChangEAvatarHandle'])->name('freelancer-em-change-avatar-handle');
    Route::get('/ve-change-avatar', [FreelancerController::class, 'VeChangEAvatar'])->name('freelancer-ve-change-avatar');
    Route::post('/ve-change-avatar-handle', [FreelancerController::class, 'VeChangEAvatarHandle'])->name('freelancer-ve-change-avatar-handle');
    Route::get('/view-orders', [FreelancerController::class, 'viewOrder'])->name('freelancer-vieworders');
    Route::get('/order-details/{id}', [FreelancerController::class, 'orderDetails']);
    Route::POST('/downloadFile', [FreelancerController::class, 'downloadAddressFIle']);
    Route::get('/upload-file', [FreelancerController::class, 'UploadFile']);
    Route::POST('/download', [FreelancerController::class, 'downloadFile']);
    Route::POST('/files', [FreelancerController::class, 'checkFiles']);
    // delivery Files
    Route::get('/upload-files/{id}', [DeliveryFileController::class, 'DeliveryPage']);
    Route::POST('/upload-delivery-files', [DeliveryFileController::class, 'UploadDeliveryFiles']);
    Route::get('/free-order-detail', [FreelancerController::class, 'FreelancerOrderDetail'])->name('freelancer-order-detail');
    Route::get('/free-get-order-detail', [FreelancerController::class, 'FreelancergetOrderDetail'])->name('freelancer-get-order-detail');
    Route::post('/order-count', [FreelancerController::class, 'FreelancerOrderCount'])->name('freelancer-order-count');


    Route::get('/profile', [FreelancerController::class, 'freelancerProfile']);
    Route::POST('/profile-update', [FreelancerController::class, 'Profileupdate']);
    Route::get('/change-password', [FreelancerController::class, 'changePassword']);
    Route::POST('/change-password-update', [FreelancerController::class, 'updatePassword']);
    Route::get('/deletefiles/{id}', [FreelancerController::class, 'DeleteFile']);
    Route::get('/filter-data', [FreelancerController::class, 'filtersData']);
    Route::get('/parameter', [FreelancerController::class, 'Parameter'])->name('freelancer-parameter');
    Route::get('/embroidery-parameter', [FreelancerController::class, 'Parameter'])->name('freelancer-embroidery-parameter');
    Route::get('/vector-parameter', [FreelancerController::class, 'Parameter'])->name('freelancer-vector-parameter');
    Route::get('/end-job-mail', [FreelancerController::class, 'EndJobMail'])->name('freelancer-end-job-mail');

    Route::get('/embroidery-freelancer-green', [FreelancerController::class, 'EmbroideryFreelancerGreenTable'])->name('embroidery-freelancer-green');
    Route::get('/embroidery-freelancer-yellow', [FreelancerController::class, 'EmbroideryFreelancerYellowTable'])->name('embroidery-freelancer-yellow');
    Route::get('/embroidery-freelancer-red', [FreelancerController::class, 'EmbroideryFreelancerRedTable'])->name('embroidery-freelancer-red');
    Route::get('/embroidery-freelancer-blue', [FreelancerController::class, 'EmbroideryFreelancerBlueTable'])->name('embroidery-freelancer-blue');
    Route::get('/embroidery-freelancer-all', [FreelancerController::class, 'EmbroideryFreelancerAllTable'])->name('embroidery-freelancer-all');
    Route::get('/embroidery-freelancer-payment', [FreelancerController::class, 'EmbroideryFreelancerPaymentTable'])->name('embroidery-freelancer-payment');
    Route::get('/embroidery-freelancer-green-dashboard', [FreelancerController::class, 'EmbroideryFreelancerGreenDashboardTable'])->name('embroidery-freelancer-green-dashboard');
    Route::get('/embroidery-freelancer-yellow-dashboard', [FreelancerController::class, 'EmbroideryFreelancerYellowDashboardTable'])->name('embroidery-freelancer-yellow-dashboard');
    Route::get('/embroidery-freelancer-red-dashboard', [FreelancerController::class, 'EmbroideryFreelancerRedDashboardTable'])->name('embroidery-freelancer-red-dashboard');
    Route::get('/embroidery-freelancer-blue-dashboard', [FreelancerController::class, 'EmbroideryFreelancerBlueDashboardTable'])->name('embroidery-freelancer-blue-dashboard');
    Route::get('/embroidery-freelancer-get-request-detail', [FreelancerController::class, 'getRequestDetail'])->name('embroidery-freelancer-get-request-detail');
    Route::get('/embroidery-freelancer-order_detail', [FreelancerController::class, 'EmOrderDetail'])->name('embroidery-freelancer-order_detail');
    Route::get('/embroidery-freelancer-startjob', [FreelancerController::class, 'StartJob'])->name('embroidery-freelancer-startjob');
    Route::get('/embroidery-freelancer-endjob', [FreelancerController::class, 'EndJob'])->name('embroidery-freelancer-endjob');
    Route::get('/embroidery-freelancer-endchange', [FreelancerController::class, 'EmbroideryEndChange'])->name('embroidery-freelancer-endchange');
    Route::get('/embroidery-payment-mail', [FreelancerController::class, 'EmbroideryPaymentMail'])->name('freelancer-embroidery-payment-mail');
    Route::get('/embroidery-payment-archive', [FreelancerController::class, 'EmbroideryPaymentArchive'])->name('freelancer-embroidery-payment-archive');
    Route::get('/em-payment-sum', [FreelancerController::class, 'EmPaymentSum'])->name('freelancer-em-payment-sum');
    Route::post('/em-payment-handle', [FreelancerController::class, 'EmPaymentHandle'])->name('freelancer-em-payment-handle');
    Route::get('/em-deletefiles/{id}', [FreelancerController::class, 'EmDeleteFile']);
    Route::get('/em-chat-get', [FreelancerController::class, 'ChatGet'])->name('freelancer-em-chat-get');
    Route::post('/em-chat', [FreelancerController::class, 'emChat'])->name('freelancer-em-chat');
    Route::get('/em-chat-long-polling', [FreelancerController::class, 'emFreelancerChatLongPolling']);
    Route::get('/em-change-mail', [FreelancerController::class, 'emChangeMail']);


    Route::get('/vector-freelancer-green', [FreelancerController::class, 'VectorFreelancerGreenTable'])->name('vector-freelancer-green');
    Route::get('/vector-freelancer-yellow', [FreelancerController::class, 'VectorFreelancerYellowTable'])->name('vector-freelancer-yellow');
    Route::get('/vector-freelancer-red', [FreelancerController::class, 'VectorFreelancerRedTable'])->name('vector-freelancer-red');
    Route::get('/vector-freelancer-blue', [FreelancerController::class, 'VectorFreelancerBlueTable'])->name('vector-freelancer-blue');
    Route::get('/vector-freelancer-all', [FreelancerController::class, 'VectorFreelancerAllTable'])->name('vector-freelancer-all');
    Route::get('/vector-freelancer-payment', [FreelancerController::class, 'VectorFreelancerPaymentTable'])->name('vector-freelancer-payment');
    Route::get('/vector-freelancer-green-dashboard', [FreelancerController::class, 'VectorFreelancerGreenDashboardTable'])->name('vector-freelancer-green-dashboard');
    Route::get('/vector-freelancer-yellow-dashboard', [FreelancerController::class, 'VectorFreelancerYellowDashboardTable'])->name('vector-freelancer-yellow-dashboard');
    Route::get('/vector-freelancer-red-dashboard', [FreelancerController::class, 'VectorFreelancerRedDashboardTable'])->name('vector-freelancer-red-dashboard');
    Route::get('/vector-freelancer-blue-dashboard', [FreelancerController::class, 'VectorFreelancerBlueDashboardTable'])->name('vector-freelancer-blue-dashboard');
    Route::get('/vector-freelancer-get-request-detail', [FreelancerController::class, 'getRequestDetail'])->name('vector-freelancer-get-request-detail');
    Route::get('/vector-freelancer-order_detail', [FreelancerController::class, 'VeOrderDetail'])->name('vector-freelancer-order_detail');
    Route::get('/vector-freelancer-endchange', [FreelancerController::class, 'VectorEndChange'])->name('vector-freelancer-endchange');
    Route::get('/vector-payment-mail', [FreelancerController::class, 'VectorPaymentMail'])->name('freelancer-vector-payment-mail');
    Route::get('/vector-payment-archive', [FreelancerController::class, 'VectorPaymentArchive'])->name('freelancer-vector-payment-archive');
    Route::get('/ve-payment-sum', [FreelancerController::class, 'VePaymentSum'])->name('freelancer-ve-payment-sum');
    Route::post('/ve-payment-handle', [FreelancerController::class, 'VePaymentHandle'])->name('freelancer-ve-payment-handle');
    Route::get('/ve-deletefiles/{id}', [FreelancerController::class, 'VeDeleteFile']);
    Route::get('/ve-chat-get', [FreelancerController::class, 'ChatGet'])->name('freelancer-ve-chat-get');
    Route::post('/ve-chat', [FreelancerController::class, 'veChat'])->name('freelancer-ve-chat');
    Route::get('/ve-chat-long-polling', [FreelancerController::class, 'veFreelancerChatLongPolling']);
    Route::get('/ve-change-mail', [FreelancerController::class, 'veChangeMail']);


});