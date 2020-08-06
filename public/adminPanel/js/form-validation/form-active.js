(function($) {
    "use strict";
    // Validation for order form Add New AdminPolicy
    $(".AddNewAdminPolicy").validate({
        rules: {
            name: {
                required: true
            },
            otpValidTimeInSeconds: {
                required: true,
                number: true,
                min: 0
            },
            access: {
                required: true
            },
            source: {
                required: true
            },
            isActive: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please enter name"
            },
            otpValidTimeInSeconds: {
                required: "Please enter OTP Valid TimeIn Seconds"
            },
            access: {
                required: "Please Select access"
            },
            source: {
                required: "Please Select source"
            },
            isActive: {
                required: "Please select Active Status"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".AddNewAdminInformation-form").validate({
        rules: {
            emailID: {
                required: true
            },
            username: {
                required: true
            },
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            },
            adminPolicyID: {
                required: true
            },
            portalProviderID: {
                required: true
            },
            accessPolicyID: {
                required: true
            },
            isActive: {
                required: true
            }
        },
        messages: {
            emailID: {
                required: "Please enter email"
            },
            username: {
                required: "Please enter user name"
            },
            password: {
                required: "Please enter password",
                minlength: "Password should atleast 6 character in length...!"
            },
            confirm_password: {
                required: "Please enter confirm password",
                equalTo: "Your passwords don't match. Try again?"
            },
            adminPolicyID: {
                required: "Please select admin policy ID"
            },
            portalProviderID: {
                required: "Please select portal provider ID"
            },
            accessPolicyID: {
                required: "Please select Access Policy ID"
            },
            isActive: {
                required: "Please select active status"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    // addNewUser
    $(".addNewUser").validate({
        rules: {
            portalProviderUUID: {
                required: true
            },
            portalProviderUserID: {
                required: true
            },
            email: {
                email: true
            },
            balance: {
                required: true
            }
        },
        messages: {
            portalProviderUUID: {
                required: "Please select portal provider"
            },
            portalProviderUserID: {
                required: "Please enter portal Provider sUserID"
            },
            email: {
                email: "Please enter a VALID email address"
            },
            balance: {
                required: "Please enter balance"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    // createAccessPolicy

    $(".createAccessPolicy").validate({
        rules: {
            name: {
                required: true
            },
            isAllowAll: {
                required: true
            },
            isActive: {
                required: true
            },
            accessAdminPolicy: {
                required: true
            },
            accessAccessPolicy: {
                required: true
            },
            accessAdminInformation: {
                required: true
            },
            accessProviderList: {
                required: true
            },
            accessProviderConfig: {
                required: true
            },
            accessCurrency: {
                required: true
            },
            accessBetRule: {
                required: true
            },
            accessBetSetup: {
                required: true
            },
            accessInvitationSetup: {
                required: true
            },
            accessProviderGameSetup: {
                required: true
            },
            accessProviderRequestList: {
                required: true
            },
            accessProviderRequestBalance: {
                required: true
            },
            accessProviderInfo: {
                required: true
            },
            accessNotification: {
                required: true
            },
            accessHolidayList: {
                required: true
            },
            accessMonetaryLog: {
                required: true
            },
            accessActivityLog: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please enter name"
            },
            isAllowAll: {
                required: "Please Select Show Portal Providers"
            },
            isActive: {
                required: "Please select Active Status"
            },
            accessAdminPolicy: {
                required: "Please select Access Admin Policy"
            },
            accessAccessPolicy: {
                required: "Please select Access Access Policy"
            },
            accessAdminInformation: {
                required: "Please select Access Admin Information"
            },
            accessProviderList: {
                required: "Please select Access Provider List"
            },
            accessProviderConfig: {
                required: "Please select Access Provider Config"
            },
            accessCurrency: {
                required: "Please select Access Currency"
            },
            accessBetRule: {
                required: "Please select Access Bet Rule"
            },
            accessBetSetup: {
                required: "Please select Access Bet Setup"
            },
            accessInvitationSetup: {
                required: "Please select Access Invitation Setup"
            },
            accessProviderGameSetup: {
                required: "Please select Access Provider Game Setup"
            },
            accessProviderRequestList: {
                required: "Please select Access Provider Request List"
            },
            accessProviderRequestBalance: {
                required: "Please select Access Provider Request Balance"
            },
            accessProviderInfo: {
                required: "Please select Access Provider Info"
            },
            accessNotification: {
                required: "Please select Access Notification"
            },
            accessHolidayList: {
                required: "Please select Access Holiday List"
            },
            accessMonetaryLog: {
                required: "Please select Access Monetary Log"
            },
            accessActivityLog: {
                required: "Please select Access Activity Log"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    // AddNewProviderConfig-form

    $(".AddNewProviderConfig-form").validate({
        rules: {
            portalProviderID: {
                required: true
            }
        },
        messages: {
            portalProviderID: {
                required: "Please Select Portal Provider"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".createCurrency-form").validate({
        rules: {
            name: {
                required: true
            },
            rate: {
                required: true,
                number: true,
                min: 0
            },
            isActive: {
                required: true
            },
            symbol: {
                required: true
            },
            abbreviation: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please Enter Name"
            },
            rate: {
                required: "Please Enter Rate"
            },
            isActive: {
                required: "Please Select Active Status"
            },
            symbol: {
                required: "Please Enter Symbol"
            },
            abbreviation: {
                required: "Please Enter Abbreviation"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".createFollowBetRule-form").validate({
        rules: {
            name: {
                required: true
            },
            type: {
                required: true
            },
            isActive: {
                required: true
            },
            min: {
                required: true,
                number: true,
                min: 0
            },
            max: {
                required: true,
                number: true,
                min: 0
            }
        },
        messages: {
            name: {
                required: "Please Enter Name"
            },
            type: {
                required: "Please Select type"
            },
            isActive: {
                required: "Please Select Active Status"
            },
            min: {
                required: "Please Enter Value Min"
            },
            max: {
                required: "Please Enter Value Max"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".createFollowBetSetup-form").validate({
        rules: {
            isActive: {
                required: true
            }
        },
        messages: {
            isActive: {
                required: "Please Select Active Status"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });


    $(".createNotification").validate({
        rules: {
            portalProviderID: {
                required: true
            },
            title: {
                required: true
            },
            message: {
                required: true
            }
        },
        messages: {
            portalProviderID: {
                required: "Please Select Portal Provider"
            },
            title: {
                required: "Please Enter Title"
            },
            message: {
                required: "Please Enter Message"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".providerInfo").validate({
        rules: {
            portalProviderPID: {
                required: true
            },
            serverName: {
                required: true
            },
            ipList: {
                required: true
            },
            APIKey: {
                required: true
            }
        },
        messages: {
            portalProviderPID: {
                required: "Please Select Portal Provider"
            },
            serverName: {
                required: "Please Enter Server Name"
            },
            ipList: {
                required: "Please Enter Ip Address"
            },
            APIKey: {
                required: "Please Enter API Key"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".addProviderList").validate({
        rules: {
            name: {
                required: true
            },
            currencyID: {
                required: true
            },
            creditBalance: {
                required: true,
                number: true,
                min: 0
            },
            mainBalance: {
                required: true,
                number: true,
                min: 0
            },
            isActive: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please Enter Name"
            },
            currencyID: {
                required: "Please Select currency Name"
            },
            creditBalance: {
                required: "Please Enter Credit Balance"
            },
            mainBalance: {
                required: "Please Enter Main Balance"
            },
            isActive: {
                required: "Please Select Active Status"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

    $(".createInvitationSetup-form").validate({
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please Enter Name"
            }
        },

        errorPlacement: function(error, element) {
            error.insertAfter(element.parent());
        }
    });

})(jQuery);