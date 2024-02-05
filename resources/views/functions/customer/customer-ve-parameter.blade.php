<script>
    $(function() {
        window.prettyPrint() && prettyPrint();
        $('[name=parameter_required_vector_file]').multiselect({
            // buttonWidth: '300px'
        });
        $('[name=parameter_required_image_file]').multiselect({
            // buttonWidth: '300px'
        });
    });
    $(function() {
        $('#customer_parameters_ve1').click(function() {
            $.ajax({
                url: '{{ __('routes.customer-get-ve-parameter') }}',
                type: 'get',
                success: (parameter) => {

                },
                error: () => {
                    console.error("error");
                }
            });
        });
        $('#customer_ve_parameter_submit').click(function() {
            var ve_parameter_data = new FormData();
            ve_parameter_data.append('parameter8', $('[name=parameter_required_vector_file]').val()
                .join(', '));
            ve_parameter_data.append('parameter9', $('[name=parameter_required_image_file]').val().join(
                ', '));
            var confirm = window.confirm('{{ __('home.click_customer_parameter_change') }}');
            if (confirm == true) {
                $.ajax({
                    url: '{{ __('routes.customer-ve-parameter-change') }}',
                    type: 'post',
                    data: ve_parameter_data,
                    contentType: false,
                    processData: false,
                    success: () => {
                        $('#customer_ve_parameter_submit').hide();
                        $('#change_ve_parameter_success_popup').modal('show');
                        $.ajax({
                            url: '{{ __('routes.customer-ve-parameter-change-mail') }}',
                            type: 'get',
                            success: () => {
                                console.log("success");
                            },
                            error: () => {
                                console.error("error");
                            }
                        })
                    },
                    error: () => {
                        console.error("error");
                    }
                })
            }

        })
    })
</script>
