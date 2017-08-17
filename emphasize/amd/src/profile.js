/* jshint ignore:start */
define(['jquery', 'jqueryui'], function($, jqui) {

            // alert('called');
                        // Edit Profile Page
            /*$('#editprofile #first_name').focusout(function() {
              var fname = $('#first_name').val();
              if(fname == ''){
                alert('called focusout event');
                $(this).parent('div.form-group').addClass('has-danger');
              }else{
                //alert(fname); 
              }

            });*/
            $('#editprofile .form-horizontal #btn-save-changes').click(function() {
                $('div#error-message').show();
                $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                $('div#error-message p').html("Saving...");
                var fname = $('#first_name').val();
                var lname = $('#surname').val();
                var emailid = $('#standard_email').val();
                var description = $.trim($('#description').val());
                var city = $.trim($('#city').val());
                var country = $('#editprofile .form-horizontal #country option:selected').val();
                // console.log(fname+lname+emailid+description+city+country);
                // return false;
                if (fname === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterfirstname', 'theme_emphasize'));
                    $('#first_name').focus();
                    return false;
                }
                if (lname === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterlastname', 'theme_emphasize'));
                    $('#surname').focus();
                    return false;
                }
                if (emailid === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enteremailid', 'theme_emphasize'));
                    $('#standard_email').focus();
                    return false;
                }
                // Validate email text
                var regEx = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
                if (!regEx.test(emailid)) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterproperemailid', 'theme_emphasize'));
                    $('#inputEmail').focus();
                    return false;
                }
                emailid = encodeURIComponent(emailid);
                /*if (country === M.util.get_string('selectcountry', 'theme_remui')) {
                    countryname = '';
                    country = '';
                }*/
                $.ajax({
                    type: "GET",
                    async: true,
                    url: M.cfg.wwwroot + '/theme/emphasize/request_handler.php?action=save_user_profile_settings&fname=' + fname + '&lname=' + lname + '&emailid=' + emailid + '&description=' + description + '&city=' + city + '&country=' + country,
                    success: function(data) {
                        // alert("Saved"+data);
                        $('div#error-message').show();
                        $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                        $('div#error-message p').html(M.util.get_string('detailssavedsuccessfully', 'theme_emphasize'));
                        $('.profile-user').text(fname + " " + lname);
                        $('.usermenu a.navbar-avatar span.username').text((fname + " " + lname));
                            $('#user-description').text( description);
                    },
                     error: function(requestObject, error, errorThrown) {
                        /*alert(error);
                        alert(errorThrown);*/
                        $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                        $('div#error-message p').html(error + ' : ' + errorThrown + ', '+ M.util.get_string('actioncouldnotbeperformed', 'theme_emphasize'));
                    }     
                });
            });        
});
/* jshint ignore:end */