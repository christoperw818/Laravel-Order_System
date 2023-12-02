<style>
    * {
        box-sizing: border-box;
    }

    .order_form_input,
    textarea,
    .ms-options-wrap>button,
    .ms-options-wrap>button:focus {
        width: 100%;
        height: 40px;
        padding: 12px;
        border: 1px solid #ddd !important;
        display: flex;
        margin: auto;
    }

    .ms-options-wrap * {
        font-size: 16px;
    }

    .ms-res-ctn {
        top: 100%;
        left: 0;
    }

    .order_form_lavel {
        display: inline-block;
    }

    .admin_order_form_submit {
        background: #c4ae79 !important;
        color: #fff !important;
        height: 40px !important;
        border: 0;
        border-radius: 0;
        font-size: 13px;
        padding: 6px 25px;
        font-family: "Inter", "Helvetica", monospace;
        float: right;
    }


    .dropdown-toggle.product-multiselect {
        height: 40px;
    }

    .dropdown-toggle.product-multiselect_em_ex {
        height: 40px;
    }

    .dropdown-toggle.product-multiselect div {
        max-width: 100%;
        overflow-y: visible;
        text-wrap: wrap;
        width: 100%;
        min-height: 100%;
        background-color: #fff;
        border: 1px solid #ddd;
    }

    .dropdown-toggle.product-multiselect_em_ex div {
        max-width: 100%;
        overflow-y: visible;
        text-wrap: wrap;
        width: 100%;
        min-height: 100%;
        background-color: #fff;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .dropdown-toggle.product-multiselect::after {
        display: none;
    }

    .dropdown-toggle.product-multiselect_em_ex::after {
        display: none;
    }

    /* .order_form_submit:hover {
        background-color: #45a049;
    }

    .order_form_submit_em_ex:hover {
        background-color: #45a049;
    } */


    .col-20 {
        float: left;
        width: 20%;
        margin-top: 10px;
    }

    .col-80 {
        float: left;
        width: 80%;
        margin-top: 6px;
    }

    .col-lg-7 {
        flex: 0 0 auto;
        width: 100%;
    }

    /* Clear floats after the columns */
    /* .row::after {
        content: "";
        display: table;
        clear: both;
    } */

    .order_form_check_label {
        margin-left: 10px;
        margin-top: -4px;
    }

    .ms-options-wrap>.ms-options>ul input[type="checkbox"] {
        margin: auto 5px auto 0;
        position: static;
    }

    .ms-ctn .ms-sel-ctn {
        margin-left: -7px;
        padding-left: 10px;
    }

    .ms-ctn .ms-trigger .ms-trigger-ico {
        display: inline-block;
        width: 0;
        height: 0;
        vertical-align: bottom;
        border-top: 4px solid #333;
        border-right: 4px solid transparent;
        border-left: 4px solid transparent;
        content: "";
        margin-left: 8px;
        margin-top: 15px;
    }

    .ms-res-ctn .ms-res-item {
        line-height: 25px;
        text-align: left;
        padding: 2px 15px;
        color: #666;
        cursor: pointer;
    }

    .clear-products-button {
        position: absolute;
        right: 10px;
        top: 0;
        height: 100%;
        border: none;
        background-color: transparent;
    }

    .btn-success {
        color: #fff;
        background-color: #c3ac6d;
        border: none;
        border-radius: 0;
    }

    .btn-success :hover {
        background-color: #c3ac6d !important;
    }

    .upload_table_button {
        color: white;
        background-color: #c3ac6d;
        border: none;
        border-radius: 0;
        padding: 5px 8px;
        width: 80px;
    }

    .order_form_validation_deliver_time,
    .order_form_validation_projectname,
    .order_form_validation_size,
    .order_form_validation_products,
    .order_form_file_upload,
    .order_form_validation_checkbox,
    .admin_search_customer_validation {
        color: red;
        font-style: italic;
        font-size: 13px;
        display: none;
        margin-bottom: 10px;
    }


    /* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
    @media screen and (max-width: 600px) {

        .col-25,
        .col-75 {
            width: 100%;
            margin-top: 0;
        }
    }
</style>
<section class="page_section">
    <div class="row">
        <div class="col-md-1 col-lg-2"></div>
        <div class="col-12 col-md-10 col-lg-8">
            <div class="pagetitle">
                {{ __('home.orderform_title') }}
            </div>
            <div style="margin-block: 10px;">
                <div class="SearchInputWrapper">
                    <div id="adminTableSearchInput">
                        <div class="tooltip">Kunden suchen
                        </div>
                    </div>
                    <div class="tooltiptext">
                        <table class="table table-striped" style="font-size:11.5px; margin-bottom:0 !important;"
                            id="customer_searched_tooltip_table">
                            <thead>
                                <tr>
                                    <th>Kundennr</th>
                                    <th>Firma</th>
                                    <th>Name</th>
                                    <th>Vorname</th>
                                    <th>Straße, Nr.</th>
                                    <th>Postleitzahl</th>
                                    <th>Ort</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="customer_serched_result_customer_number"></td>
                                    <td id="customer_serched_result_company"></td>
                                    <td id="customer_serched_result_name"></td>
                                    <td id="customer_serched_result_first_name"></td>
                                    <td id="customer_serched_result_street_number"></td>
                                    <td id="customer_serched_result_postal_code"></td>
                                    <td id="customer_serched_result_location"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="admin_search_customer_validation">
                    Suchen Sie einen Kunden, der bestellen muss
                </div>

            </div>
            <form id="admin_order_submit_form" action="">
                <input type="hidden" name="type">
                <input type="hidden" name="deliver_time">
                <input type="hidden" name="customer_number" value="" />
                <input type="hidden" name="ordered_from" value="" />
                <input type="hidden" name="searched_id" value="" />
                <div id="order_form_project_name" class="order_form_class">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-xl-2">
                            <label class="order_form_lavel" for="projectname">{{ __('home.projectname') }} <span
                                    class="reqiurd">*</span></label>
                        </div>
                        <div class="col-12 col-sm-9 col-xl-10">
                            <input type="text" class="order_form_input" name="project_name"
                                placeholder="{{ __('home.order_form-projectname_placeholder') }}">
                            <div class="order_form_validation_projectname">
                                {{ __('home.validation_project_name') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div id="order_form_size" class="order_form_class">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-xl-2">
                            <label class="order_form_lavel" for="size">{{ __('home.size') }} <span
                                    class="reqiurd">*</span>
                            </label>
                        </div>
                        <div class="col-12 col-sm-9 col-xl-10">
                            <div style="display: flex">
                                <div style="display: flex">
                                    <div style="display: flex;">
                                        <input type="text" class="order_form_input" id="input_number_format"
                                            name="size"><span style="display:flex; margin:auto 10px;">mm</span>
                                    </div>
                                </div>
                                <div style="display:flex; margin:auto;">
                                    <div class="input-group">
                                        <input type="radio" id="order_form_width" name="width_height" value="Breite"
                                            checked>
                                        <label class="order_form_lavel" style="margin-left: 5px;"
                                            for="Width">{{ __('home.width') }}</label>
                                        <input type="radio" id="order_form_heght" name="width_height" value="Höhe"
                                            style="margin-left: 20px;">
                                        <label class="order_form_lavel" style="margin-left: 5px;"
                                            for="Height">{{ __('home.height') }}</label>
                                    </div>
                                </div>
                                <div class="order_form_validation_size">
                                    {{ __('home.validation_size') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="products">
                <div id="order_form_products" class="order_form_class">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-xl-2">
                            <label class="order_form_lavel" for="projectname">{{ __('home.fianl_product') }} <span
                                    class="reqiurd">*</span></label>
                        </div>
                        <div class="col-12 col-sm-9 col-xl-10">
                            <div class="dropdown">
                                <div class="product-multiselect dropdown-toggle" href="#">
                                    <div id="selected_products" style="padding: 12px">
                                    </div>
                                </div>
                                <div class="product-item-menu">
                                    <div class="row product-select-items">
                                        <div class="col-6 col-xl-3">
                                            <div>
                                                <input type="checkbox" value="{{ __('home.working pants') }}"
                                                    name="example" />
                                                {{ __('home.working pants') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.working jackets') }}"
                                                    name="example" />
                                                {{ __('home.working jackets') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.cotton bag') }}"
                                                    name="example" />
                                                {{ __('home.cotton bag') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.canvas') }}"
                                                    name="example" />
                                                {{ __('home.canvas') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.caps') }}"
                                                    name="example" />
                                                {{ __('home.caps') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.blanket') }}"
                                                    name="example" />
                                                {{ __('home.blanket') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.felt') }}"
                                                    name="example" />
                                                {{ __('home.felt') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.felt hats') }}"
                                                    name="example" />
                                                {{ __('home.felt hats') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.fleece') }}"
                                                    name="example" />
                                                {{ __('home.fleece') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.fleece scarf') }}"
                                                    name="example" />
                                                {{ __('home.fleece scarf') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.frottee') }}"
                                                    name="example" />
                                                {{ __('home.frottee') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.dishtowels') }}"
                                                    name="example" />
                                                {{ __('home.dishtowels') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.towels') }}"
                                                    name="example" />
                                                {{ __('home.towels') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.dress shirt') }}"
                                                    name="example" />
                                                {{ __('home.dress shirt') }}
                                            </div>
                                        </div>
                                        <div class="col-6 col-xl-3">
                                            <div>
                                                <input type="checkbox" value="{{ __('home.jacket') }}"
                                                    name="example" />
                                                {{ __('home.jacket') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.jeans jacket') }}"
                                                    name="example" />
                                                {{ __('home.jeans jacket') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.tunics') }}"
                                                    name="example" />
                                                {{ __('home.tunics') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.overall') }}"
                                                    name="example" />
                                                {{ __('home.overall') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.chef jacket') }}"
                                                    name="example" />
                                                {{ __('home.chef jacket') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.dungarees') }}"
                                                    name="example" />
                                                {{ __('home.dungarees') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.baby bib') }}"
                                                    name="example" />
                                                {{ __('home.baby bib') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.leather') }}"
                                                    name="example" />
                                                {{ __('home.leather') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.leather shoes') }}"
                                                    name="example" />
                                                {{ __('home.leather shoes') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.linen fabric') }}"
                                                    name="example" />
                                                {{ __('home.linen fabric') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.loden fabric') }}"
                                                    name="example" />
                                                {{ __('home.loden fabric') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.knitted hat') }}"
                                                    name="example" />
                                                {{ __('home.knitted hat') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.work overall') }}"
                                                    name="example" />
                                                {{ __('home.work overall') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.patch') }}"
                                                    name="example" />
                                                {{ __('home.patch') }}
                                            </div>
                                        </div>
                                        <div class="col-6 col-xl-3">
                                            <div>
                                                <input type="checkbox" value="{{ __('home.patch material') }}"
                                                    name="example" />
                                                {{ __('home.patch material') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.plush') }}"
                                                    name="example" />
                                                {{ __('home.plush') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.polo') }}"
                                                    name="example" />
                                                {{ __('home.polo') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.backback') }}"
                                                    name="example" />
                                                {{ __('home.backback') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.sauna coat') }}"
                                                    name="example" />
                                                {{ __('home.sauna coat') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.saddle cloth') }}"
                                                    name="example" />
                                                {{ __('home.saddle cloth') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.chef apron') }}"
                                                    name="example" />
                                                {{ __('home.chef apron') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.silk') }}"
                                                    name="example" />
                                                {{ __('home.silk') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.napkins') }}"
                                                    name="example" />
                                                {{ __('home.napkins') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.socks') }}"
                                                    name="example" />
                                                {{ __('home.socks') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.softshell') }}"
                                                    name="example" />
                                                {{ __('home.softshell') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.quilted vest') }}"
                                                    name="example" />
                                                {{ __('home.quilted vest') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.baby body') }}"
                                                    name="example" />
                                                {{ __('home.baby body') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.knitted jackets') }}"
                                                    name="example" />
                                                {{ __('home.knitted jackets') }}
                                            </div>
                                        </div>
                                        <div class="col-6 col-xl-3">
                                            <div>
                                                <input type="checkbox" value="{{ __('home.knitted material') }}"
                                                    name="example" />
                                                {{ __('home.knitted material') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.knitted sweat') }}"
                                                    name="example" />
                                                {{ __('home.knitted sweat') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.knit headband') }}"
                                                    name="example" />
                                                {{ __('home.knit headband') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.sweatshirt') }}"
                                                    name="example" />
                                                {{ __('home.sweatshirt') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.bags') }}"
                                                    name="example" />
                                                {{ __('home.bags') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.tablecloths') }}"
                                                    name="example" />
                                                {{ __('home.tablecloths') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.trainingsjackets') }}"
                                                    name="example" />
                                                {{ __('home.trainingsjackets') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.jersey') }}"
                                                    name="example" />
                                                {{ __('home.jersey') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.t-shirt') }}"
                                                    name="example" />
                                                {{ __('home.t-shirt') }}
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.vest') }}"
                                                    name="example" />
                                                {{ __('home.vest') }}
                                            </div>
                                            <div>
                                                <input id="manualInput" class="order_form_input" type="text"
                                                    placeholder="{{ __('home.manual_input') }}"
                                                    style="height:30px; margin: 10px 0; ">
                                            </div>
                                            <div>
                                                <input type="checkbox" value="{{ __('home.Not specified') }}"
                                                    name="example" />
                                                {{ __('home.Not specified') }}
                                            </div>
                                        </div>
                                        <div style="display: flex; justify-content: flex-end; margin-top: 20px">
                                            <button type="button" id="close_project_menu" class="btn btn-secondary "
                                                style="width:200px; height: 30px; padding: 0; background-color: #c3ac6d; border:none; border-radius:0; font-size:13px;glyphicon glyphicon-upload">{{ __('home.close') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="order_form_validation_products">
                                {{ __('home.validation_products') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div id="order_form_instruction" class="order_form_class">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-xl-2">
                            <label class="order_form_lavel"
                                for="special_instructions">{{ __('home.special instructions') }}</label>
                        </div>
                        <div class="col-12 col-sm-9 col-xl-10">
                            <textarea id="order_form_textarea" name="special_instructions"
                                placeholder="{{ __('home.order_form_textarea_placeholder') }}" style="height:100px"></textarea>
                        </div>
                    </div>
                </div>
                <div id="order_form_upload" class="order_form_class">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-xl-2">
                            <label class="order_form_lavel" for="">{{ __('home.data_upload') }} <span
                                    class="reqiurd">*</span></label>
                        </div>
                        <div class="col-12 col-sm-9 col-xl-10">
                            <!-- The file upload form used as target for the file upload widget -->
                            <div id="fileupload" action="" method="POST" enctype="multipart/form-data">
                                <!-- Redirect browsers with JavaScript disabled to the origin page -->
                                <noscript><input type="hidden" name="redirect" value="" /></noscript>
                                <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                                <div class="row fileupload-buttonbar">
                                    <div class="col-lg-7">
                                        <!-- The fileinput-button span is used to style the file input field as button -->
                                        <span class="fileinput-button">
                                            <i class="glyphicon glyphicon-plus"></i>
                                            <span style="font-size: 13px;">{{ __('home.add_file') }}...</span>
                                            <input type="file" name="files[]" multiple
                                                id="order_form_file_input" />
                                        </span>
                                        <button type="submit" class="btn btn-primary start"
                                            style="visibility: hidden;">
                                            <i class="glyphicon glyphicon-upload"></i>
                                            <span>Start Upload</span>
                                        </button>

                                        <span class="fileupload-process"></span>
                                    </div>
                                    <div class="order_form_file_upload">
                                        {{ __('home.validation_file_upload') }}
                                    </div>
                                    <!-- The global progress state -->
                                    <div class="col-lg-5 fileupload-progress fade">
                                        <!-- The global progress bar -->
                                        <div class="progress progress-striped active" role="progressbar"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar progress-bar-success" style="width: 0%;">
                                            </div>
                                        </div>
                                        <!-- The extended global progress state -->
                                        {{-- <div class="progress-extended">&nbsp;</div> --}}
                                    </div>
                                </div>
                                <!-- The table listing the files available for upload/download -->
                                <table role="presentation" class="table table-striped" id="order_form_upload_list">
                                    <tbody class="files"></tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <br>

                <div class="row" style="display: flex; justify-content:flex-end">
                    <div>
                        <button type="button" class="admin_order_form_submit">{{ __('home.submit') }}</button>
                    </div>
                </div>
            </form>

        </div>
        <div class="col-md-1 col-lg-2"></div>

</section>
@include('components.user.order_form_success')
@include('components.admin.customer_search_modal')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function() {
        $('#adminTableSearchInput').click(function() {
            $('#customer_search_popup').modal('show');
        })
    });
    $(function() {
        $('#admin_order_form_file_input').on('change', function() {
            var files = $(this)[0].files;
            for (var i = 0; i < files.length; i++) {
                var fileName = files[i].name;
                var fileExtension = fileName.split('.').pop().toLowerCase();
                var fileSize = files[i].size;
                if ($.inArray(fileExtension, ['exe', 'bat']) !== -1) {
                    alert('You cannot upload .exe or .bat files');
                    $('#order_form_upload_list tr').remove();
                    return;
                }
                if (fileSize > 25 * 1024 * 1024) {
                    alert('File size should not exceed 25 MB');
                    $('#order_form_upload_list tr').remove();
                    return;
                }
            }
        });

    })
</script>
