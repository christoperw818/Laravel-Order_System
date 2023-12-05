<section class="page_section">
    <div class="row">
        <div class="col-xl-1"></div>
        <div class="col-12 col-xl-10">
            <div class="pagetitle">Abrechnung Stickprogramme
            </div>
            <div>
                <div>
                    <div class="responsive-table">
                        <button id="em_admin_payment_table_reload_button" style="display: none"></button>
                        <table id="em_admin_payment_table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align:center;">
                                        {{ __('home.order_type') }}</th>
                                    <th>{{ __('home.delivery_time') }}</th>
                                    <th>{{ __('home.order') }}</th>
                                    <th>{{ __('home.date') }}</th>
                                    <th>{{ __('home.project') }}</th>
                                    <th style="text-align:center !important;">
                                        Zahl zählen</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="upload_btn">
                        <button class="btn btn-primary btn-block" id="admin_em_payment_archive">Zahlungsarchiv</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-1"></div>
    </div>

</section>
@include('components.admin.admin-em-payment-archive')