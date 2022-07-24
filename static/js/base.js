class BaseControl{
    constructor(){
        this.pageInit();
        this.toggleNavbars();
        this.togglePhoneMenu();
    }

    pageInit(){
        // Copyright
        const currentYear = new Date().getFullYear();
        $('#copyright').html(
            `Copyright &copy ${currentYear}`
        );
    }

    toggleNavbars(){
        window.onresize = () => {
            if(window.innerWidth > 900){
                $('#phone-menu-wrapper').css('left','-10000px');
            }
        }
    }

    togglePhoneMenu(){
        // Close
        $('#alternate-close').click(function(){
            
            $('#phone-menu-wrapper').css({'left':'-10000px','transition':'all .5s ease-in-out'});
            $('body').css('overflow','auto');
        });
        // Open
        $('#hamburger').click(function(){
            $('#phone-menu-wrapper').css({'left':'0px','transition':'all .5s ease-in-out'});
            setTimeout(() => $('#phone-menu-wrapper').css('transition','none'),600);
            $('body').css('overflow','hidden');
        });
    }

    startLoading(ele){
        $(ele).html(
            `<div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>    
        `).attr('disabled',true)
    }

    endLoading(ele,text){
        $(ele).html(text).removeAttr('disabled');
    }

    validateFields(elements){
        let allFieldsValid = true;
        for(let ele of elements){
            let value = $(ele).val();
            if(value){
                $(ele).removeClass('is-invalid');
                $(ele).addClass('is-valid');
            }
            else{
                $(ele).addClass('is-invalid');
                allFieldsValid = false;
            }
        }
        return allFieldsValid;
    }

    showToast(message){
        $('#toastBody').text(message);
        const informationToast = new bootstrap.Toast('#toast',{});
        informationToast.show();
    }

    fetchData(
        url,
        data,
        bntText,
        btnId,
        get=true,
        success = () => {} 
        ,error = () => {},
    )
    {
        const inst = this;
        inst.startLoading(btnId);
        // $.post(
        //     url,
        //     data,
        //     function(res,statusCode,{status}){
        //         baseControl.endLoading(btnId,bntText);
        //         success(res,statusCode,status)
        //     }
        // )
        // .fail(() => {
        //     baseControl.endLoading(btnId,bntText);
        //     baseControl.showToast("The server has encounted an error.")
        //     error();
        // })
        $.ajax({
            url: url,
            method : get ? "GET" : "POST",
            data: data,
            success: function(res,statusCode,{status}){
                inst.endLoading(btnId,bntText);
                success(res,statusCode,status)
            },
            error: function({status}){
                inst.endLoading(btnId,bntText);
                inst.showToast("The server has encounted an error.")
                error(status);
            },
            dataType: 'json',
        })
        // .always(()=> {
        //     inst.endLoading(btnId,bntText);
        //     inst.showToast( "Check your internet connection and try again.");
        // })
    }

    deleteItem(url,){

    }
}

const baseControl = new BaseControl();