<section class="page_section">
    <div class="row">
        <div class="col-md-1 col-xl-2"></div>
        <div class="col-12 col-md-10 col-xl-8">
            <div class="pagetitle">
                {{ __('home.right_top_box_name') }}
            </div>
            <div class="tab-content">
                <div id="addresses" class="tab-pane fade in active" style="height: 100%">
                    <div class="employee-list-container"
                        style="height: 100%; display:flex; flex-direction:column; justify-content:space-between; align-items:start;">
                        <div class="employee-list" style="width: 100%; font-size:13px">
                            <table id="customer_staffs" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="padding-left: 30px;">{{ __('home.lastName') }},
                                            {{ __('home.firstName') }}</th>
                                        <th>{{ __('home.email_address') }}</th>
                                        <th>{{ __('home.created_on') }}</th>
                                        <th style="text-align: center">{{ __('home.edit') }}</th>
                                        <th style="text-align: center">{{ __('home.delete') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="employee-top d-flex" style="align-items:flex-end">
                            <div class="submit_btn" id="customer_staff_create_button" type="button">
                                {{ __('home.add') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1 col-xl-2"></div>
    </div>

</section>
@include('components.user.create-customer-staff')
