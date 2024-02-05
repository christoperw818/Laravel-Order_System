<div id="wrapper">
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            @if (auth()->user()->user_type == 'customer')
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup1"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>
                                    {{ __('home.order_standard') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'employer')
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup1"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>
                                    {{ __('home.order_standard') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_green" id="em_freelancer_green" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/New.svg') }}" style="width: 38px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.new') }}<br>{{ __('home.orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_green" id="ve_freelancer_green" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/New.svg') }}" style="width:38px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.new') }}<br>{{ __('home.orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_customer_list" id="admin_customer_list1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/users-duotone.svg') }}" style="width:38px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>AKTUELLE<br />KUNDENLISTE</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup2"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'employer')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup2"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_yellow" id="em_freelancer_yellow" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Process.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.orderes in progress') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_yellow" id="ve_freelancer_yellow" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Process.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.orderes in progress') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_add_customer" id="admin_add_customer1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/user-plus-duotone.svg') }}" style="width:38px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>KUNDE<br />ERFASSEN</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup3"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>
                            <div class="sidebar_explain">
                                <p>{{ __('home.order_standard') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'employer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup3"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>
                            <div class="sidebar_explain">
                                <p>{{ __('home.order_standard') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_red" id="em_freelancer_red" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Done.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0; ">
                                <p>{{ __('home.completed orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_red" id="ve_freelancer_red" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Done.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0;">
                                <p>{{ __('home.completed orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup1"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>
                                    {{ __('home.order_standard') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup4"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'employer')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup4"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>
                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_blue" id="em_freelancer_blue" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Changes.svg') }}" style="width: 40px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.orders') }}<br>{{ __('home.changes') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_blue" id="ve_freelancer_blue" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Changes.svg') }}" style="width: 40px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.orders') }}<br>{{ __('home.changes') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup2"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/reel-duotone.svg') }}" style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="view_order_popup" id="view_order_popup1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/list-radio-duotone.svg') }}" style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>ALLE<br>AUFTRAGE</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_all" id="em_freelancer_all" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/All.svg') }}" style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.all') }}<br>{{ __('home.orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_all" id="ve_freelancer_all" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/All.svg') }}" style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.all') }}<br>{{ __('home.orders') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup3"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_standard') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="profile_popup" id="profile_popup1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/address-card-duotone.svg') }}"
                                    style="width: 37px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.customer_master_data') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_payment" id="em_freelancer_payment" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/circle-euro-duotone.svg') }}"
                                    style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>ORDER<br>COUNTING</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="freelancer_payment" id="ve_freelancer_payment" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/circle-euro-duotone.svg') }}"
                                    style="width: 32px;" alt="sidbar_icon" />
                            </div>

                            <div class="sidebar_explain">
                                <p>ORDER<br>COUNTING</p>
                            </div>
                        </div>
                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="order_form_em_standard_popup" id="order_form_em_standard_popup4"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/vector-polygon-duotone.svg') }}"
                                    style="width: 29px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.order_express') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="login_information" id="login_information1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/users-between-lines-duotone.svg') }}"
                                    style="width: 41px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>{{ __('home.employee_access') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li style="padding-left: 0 !important;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_green" id="admin_green1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/New.svg') }}" style="width: 38px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>NEUE<br>AUFTRÄGE</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="customer_parameters_em" id="customer_parameters_em1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/grip-sharp-solid.svg') }}" style="width: 28px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0;">
                                <p>{{ __('home.customer_parameters_em_sidebar') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_yellow" id="admin_yellow1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Process.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>AUFTRÄGE IN ARBEIT</p>
                            </div>
                        </div>

                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'customer')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="customer_parameters_ve" id="customer_parameters_ve1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/grip-vertical-sharp-solid.svg') }}"
                                    style="width: 20px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0;">
                                <p>{{ __('home.customer_parameters_ve_sidebar') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
            @elseif (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_red" id="admin_red1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Done.svg') }}" style="width: 24px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0; ">
                                <p>ABGESCHLOSSENE AUFTRÄGE</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif
            @if (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_blue" id="admin_blue1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/Changes.svg') }}" style="width: 40px;" alt="sidbar_icon"/>
                            </div>
                            <div class="sidebar_explain">
                                <p>AUFTRÄGE ÄNDERUNGEN</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'admin')
                <li style="margin-right: 20px;">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_all" id="admin_all1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/All.svg') }}" style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>ALLE<br>AUFTRÄGE</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @if (auth()->user()->user_type == 'admin')
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_customer_parameters_em" id="admin_customer_parameters_em1"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/grip-sharp-solid.svg') }}" style="width: 28px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0;">
                                <p>{{ __('home.customer_parameters_em_sidebar') }}</p>
                            </div>
                        </div>

                    </div>
                </li>
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_customer_parameters_ve" id="admin_customer_parameters_ve1"
                            class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/grip-vertical-sharp-solid.svg') }}"
                                    style="width: 20px;" alt="sidbar_icon"/>
                            </div>

                            <div style="height: 40%;padding: 3px 0;">
                                <p>{{ __('home.customer_parameters_ve_sidebar') }}</p>
                            </div>
                        </div>
                    </div>
                </li>
                <li style="margin-left: 20px">
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_em_payment" id="admin_em_payment1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/circle-euro-duotone.svg') }}"
                                    style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>ABRECHNUNG STICK</p>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="sidebar-div" type="button">
                        <div lion-pop-id="admin_ve_payment" id="admin_ve_payment1" class="lion_pop_btn">
                            <div style="height: 54%;margin-bottom: 5px;padding: 0;">
                                <img src="{{ asset('asset/images/circle-euro-duotone.svg') }}"
                                    style="width: 32px;" alt="sidbar_icon"/>
                            </div>

                            <div class="sidebar_explain">
                                <p>ABRECHNUNG VEKTOR</p>
                            </div>
                        </div>
                    </div>
                </li>
            @endif
        </ul>
    </div>


    <div id="profile_popup" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'customer')
                <x-user.customer-profile />
            @endif
        </div>
    </div>

    <div id="view_order_popup" class="lion_popup_wrrpr">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'customer')
                <x-user.vieworders />
            @endif
        </div>
    </div>

    <div id="order_form_em_standard_popup" class="lion_popup_wrrpr">
        <div class="lion_popup_dv">

            @if (auth()->user()->user_type == 'customer')
                <x-user.order_form />
            @elseif (auth()->user()->user_type == 'admin')
                <x-admin.admin_order_form />
            @elseif (auth()->user()->user_type == 'employer')
                <x-user.employer_order_form />
            @endif
        </div>
    </div>
    <div id="customer_parameters_em" class="lion_popup_wrrpr">
        <div class="lion_popup_dv">

            @if (auth()->user()->user_type == 'customer')
                <x-user.customer_parameters_em />
            @endif
        </div>
    </div>
    <div id="customer_parameters_ve" class="lion_popup_wrrpr">
        <div class="lion_popup_dv">

            @if (auth()->user()->user_type == 'customer')
                <x-user.customer_parameters_ve />
            @endif
        </div>
    </div>
    <div id="login_information" class="lion_popup_wrrpr">
        <div class="lion_popup_dv">

            @if (auth()->user()->user_type == 'customer')
                <x-user.login-information />
            @endif
        </div>
    </div>

    {{-- freelancer popup --}}
    <div id="freelancer_green" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_green />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_green />
            @endif
        </div>
    </div>
    <div id="freelancer_yellow" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_yellow />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_yellow />
            @endif
        </div>
    </div>
    <div id="freelancer_red" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_red />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_red />
            @endif
        </div>
    </div>
    <div id="freelancer_blue" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_blue />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_blue />
            @endif
        </div>
    </div>
    <div id="freelancer_all" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_all />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_all />
            @endif
        </div>
    </div>
    <div id="freelancer_payment" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <x-freelancer.embroidery.em_freelancer_payment />
            @elseif(auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <x-freelancer.vector.ve_freelancer_payment />
            @endif
        </div>
    </div>

    {{-- admin popup --}}
    <div id="admin_customer_list" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.customer_list />
            @endif
        </div>
    </div>
    <div id="admin_add_customer" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.add-customer />
            @endif
        </div>
    </div>
    <div id="admin_green" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_green />
            @endif
        </div>
    </div>
    <div id="admin_yellow" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_yellow />
            @endif
        </div>
    </div>
    <div id="admin_red" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_red />
            @endif
        </div>
    </div>
    <div id="admin_blue" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_blue />
            @endif
        </div>
    </div>
    <div id="admin_all" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_all />
            @endif
        </div>
    </div>
    <div id="admin_customer_parameters_em" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_customer_parameters_em />
            @endif
        </div>
    </div>
    <div id="admin_customer_parameters_ve" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin_customer_parameters_ve />
            @endif
        </div>
    </div>
    <div id="admin_em_payment" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin-em-payment />
            @endif
        </div>
    </div>
    <div id="admin_ve_payment" class="lion_popup_wrrpr {{ session()->has('sidebar') ? 'active' : '' }}">
        <div class="lion_popup_dv">
            @if (auth()->user()->user_type == 'admin')
                <x-admin.admin-ve-payment />
            @endif
        </div>
    </div>

    @include('components.freelancer.embroidery.em_freelancer_request')
    @include('components.freelancer.vector.ve_freelancer_request')
    @include('components.freelancer.free-order-detail')
    @include('components.admin.admin-order-detail')
    @include('components.admin.admin_order_change')
    @include('components.admin.admin_order_request')
    @include('components.user.order-detail')
    @include('components.user.order-change')
    @include('components.user.order-reqeust')
    @include('components.user.create-customer-staff')
    @include('components.admin.admin-em-payment-archive')
    @include('components.admin.admin-ve-payment-archive')
    @include('components.admin.customer_parameters_em')
    @include('components.admin.customer_parameters_ve')
    @include('components.freelancer.embroidery.em_freelancer_payment_archive')
    @include('components.freelancer.vector.ve_freelancer_payment_archive')
    @include('components.admin.customer_search_modal')
    @include('components.admin.customer_profile_edit')
    @include('components.admin.customer_profile_request_handle')
</div>
