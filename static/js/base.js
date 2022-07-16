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
}

const baseControl = new BaseControl();