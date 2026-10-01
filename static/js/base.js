class BaseControl {
    constructor() {
        this.pageInit();
        this.toggleNavbars();
        this.togglePhoneMenu();
    }

    toggleEventState(ele, action, event = 'click') {
        $(ele).off(event);
        action();
    }

    pageInit() {
        // Copyright
        const currentYear = new Date().getFullYear();
        $('#copyright').html(
            `Copyright &copy ${currentYear}`
        );
    }

    toggleNavbars() {
        window.onresize = () => {
            if (window.innerWidth > 900) {
                $('#phone-menu-wrapper').css('left', '-10000px');
            }
        }
    }

    togglePhoneMenu() {
        // Close
        $('#alternate-close').click(function() {
            $('#phone-menu-wrapper').css({ 'left': '-10000px', 'transition': 'all .5s ease-in-out' });
            $('body').css('overflow', 'auto');
        });
        // Open
        $('#hamburger').click(function() {
            $('#phone-menu-wrapper').css({ 'left': '0px', 'transition': 'all .5s ease-in-out' });
            setTimeout(() => $('#phone-menu-wrapper').css('transition', 'none'), 600);
            $('body').css('overflow', 'hidden');
        });
    }

    startLoading(ele) {
        $(ele).html(
            `<div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>    
        `).attr('disabled', true)
    }

    endLoading(ele, text) {
        $(ele).html(text).removeAttr('disabled');
    }

    validateFields(elements) {
        let allFieldsValid = true;
        for (let ele of elements) {
            let value = $(ele).val().trim();
            if (value) {
                $(ele).removeClass('is-invalid');
                $(ele).addClass('is-valid');
            } else {
                $(ele).addClass('is-invalid');
                allFieldsValid = false;
            }
        }
        return allFieldsValid;
    }

    validateEmail(ele) {
        let isValid = true;
        const emailRegex = /^[a-zA-Z][a-zA-Z0-9]+@[a-z]+\.[a-z]{2,}(\.[a-z]{2,})?$/;
        const value = $(ele).val();
        if (value) {
            if (emailRegex.test(value)) {
                $(ele).removeClass('is-invalid');
                $(ele).addClass('is-valid');
            } else {
                $(ele).addClass('is-invalid');
                isValid = false;
            }
        } else {
            $(ele).removeClass('is-valid');
            $(ele).removeClass('is-invalid');
        }
        return isValid;
    }

    showToast(message) {
        $('#toastBody').text(message);
        const informationToast = new bootstrap.Toast('#toast', {});
        informationToast.show();
    }

    // FIXED fetchData method
    fetchData(
        url,
        data,
        btnText = '',
        btnId = '#',
        get = true,
        success = () => {},
        error = () => {},
    ) {
        const inst = this;
        inst.startLoading(btnId);
        $.ajax({
            url: url,
            method: get ? "GET" : "POST",
            data: data,
            success: function(res, statusCode, xhr) {
                inst.endLoading(btnId, btnText);
                // Pass the response, status code, and status text
                success(res, statusCode, xhr.statusText);
            },
            error: function(xhr) {
                const { status } = xhr;
                console.error('AJAX Error:', xhr);
                inst.endLoading(btnId, btnText);
                if (status === 500) {
                    inst.showToast("The server has encountered an error.");
                }
                error(status, xhr.responseText);
            },
            dataType: 'json',
        });
    }

    capitalize(word) {
        const wordLst = word.trim().split(' ');
        let capitalizedWordLst = [];
        for (let i of wordLst) {
            capitalizedWordLst.push(`${i[0].toUpperCase()}${i.substring(1)}`);
        }
        return capitalizedWordLst.join(' ');
    }

    colorCodesForProgress(progress) {
        if (progress >= 0 && progress <= 25) {
            return 'below-average';
        } else if (progress >= 26 && progress <= 50) {
            return 'average';
        } else if (progress >= 51 && progress <= 75) {
            return 'above-average';
        }
        return 'excellent';
    }
}

const baseControl = new BaseControl();