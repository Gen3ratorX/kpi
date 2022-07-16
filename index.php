<?php
    // Login required
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> -->
    <link rel="stylesheet" href="static/css//bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="static/css/base.css">
    <title>Admin</title>
    <style>
        :root{
            --barberry: #e6eb1e;
            --salem: #069243;
            --orange-roughy: #d4431e;
            --apple: #619939;
            --dune: #2a2824;
            --xanadu: #7c7d7c;
            --jewel: #10562c;
            --lima: #7ccc1e;
            --forest-green: #38a424;
        }

        /* width */
        ::-webkit-scrollbar {
            width: 10px;
        }


        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #666;
            border-radius: 2px;
        }

        body{
            font-family: 'Roboto', sans-serif;
        }
        a{
            text-decoration: none;
        }

        /* Navbar */
        nav{
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            background-color: white;
            z-index: 5;
        }
        #nav-brand{
            text-decoration: none;
            max-width: 380px;
        }
        #nav-brand h1, #phone-menu > a > h1, footer > a > h1{
            font-size: 22px;
            margin-left: 5px;
            font-weight: 400;
            color: var(--jewel);
            transition: all .2s ease-in-out;
        }
        #nav-brand img{
            width: 90px;
            transition: all .2s ease-in-out;
        }
        /* Hamburger menu */
        #hamburger{
            position: absolute;
            top: 27.9px;
            right: 20px;
            width: 34px;
            height:22px;
            cursor: pointer;
            display: none;
        }
        #hamburger > p{
            width: 30px;
            height: 3px;
            background-color: var(--apple);
            position: relative;
            right: -2px;
            top: 9px;
        }
        #hamburger > p::before, #hamburger > p::after{
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--apple);
            position: relative;
            top: -7px;
        }
        #hamburger > p::after{
            background-color: var(--apple);
            top: 5px;
        }
        /* Desktop menu */
        #desktop-menu > ul{
            list-style: none;
            margin: 0;
            padding: 0;
        }
        #desktop-menu > ul > li{
            display: inline-block;
        }
        #desktop-menu > ul > li > a, #phone-menu > ul > li > a{
            color: var(--dune);
            font-size: 15px;
            font-weight: 500;
            padding: 8px 20px;
            transition: all .2s ease-in-out;
            display: block;
            text-align: center;
        }
        #desktop-menu > ul > li > a:hover, #phone-menu > ul > li > a:hover{
            color: var(--forest-green);
        }
        .role > a{
            background-color: var(--forest-green);
            color: white !important;
            border-radius: 5px;
            border: 2px solid var(--forest-green);
            width: 150px;
        }
        .role > a:hover{
            color: var(--forest-green) !important;
            background-color: white;
        }
        .logout > a:hover{
            color: crimson !important;
        }

        /* Phone Menu */
        #phone-menu-wrapper{
            position: fixed;
            width: 100vw;
            height: 100vh;
            top: 0;
            left: -10000px;
            z-index: 10;
        }
        #phone-menu{
            /* width: 250px; */
            background-color: var(--lima);
            background-color: white;
            height: 100vh;
            padding: 10px;
            flex-grow: 0;
        }
        #phone-menu > a{
            text-align: center;
            display: block;
        }
        #phone-menu > a > h1{
            color: var(--jewel);
            font-size: 20px;
            margin-top: 15px;
            font-weight: 400;
        }
        #phone-menu > ul{
            list-style: none;
            margin: 40px 0px;
            padding: 0;
            overflow: auto;
            height: calc(100vh - 210px);
        }
        #phone-menu > ul > li:first-child > a{
            border-top: thin solid rgba(0,0,0,.125);
        }
        #phone-menu > ul > li > a{
            border-bottom: thin solid rgba(0,0,0,.125);
            font-size: 14px;
        }
        #phone-menu .role > a{
            width: 100%;
            border-bottom: 2px solid var(--apple);
            margin: 10px 0;
        }
        #alternate-close{
            background-color: rgba(0,0,0,.6);
            flex-grow: 1;
        }

        /* Main body */
        #main-body{
            margin-top: 78px;
        }

        /* Footer */
        footer{
            background-color: #222;
        }
        footer > a{
            padding: 20px;
            text-align: center;
            display: block;
        }
        footer > a > h1{
            margin-top: 15px;
        }
        #copyright{
            text-align: center;
            padding: 10px;
            font-weight: 400;
            background-color: #333;
            font-size: 15px;
            color: #999;
        }

        /* 0px - 900px */
        @media only screen and (max-width:900px){
            #hamburger{
                display: block;
            }
            #desktop-menu{
                display: none;
            }
        }

        /* 0px - 500px */
        @media only screen and (max-width:500px){
            /* Nav */
            #nav-brand h1{
                font-size: 20px;
            }
            #nav-brand img{
                width: 80px;
            }
            /* Hamburger */
            #hamburger{
                top: 25.5px;
            }
            #main-body{
                margin-top: 74px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="py-2 px-4 border-bottom">
        <!-- Hamburger -->
        <div id="hamburger">
            <p></p>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <!-- Nav brand -->
            <a href="index.php" class="d-flex align-items-center" id="nav-brand">
                <img src="static/images/nla-logo.png" width="auto" alt="NLA">
                <h1>National Lottery Authority <br> Key Performance Index</h1>
            </a>
            <!-- Desktop Menu -->
            <div id="desktop-menu">
                <ul>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="role">
                        <a href="">Add Role</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Phone Menu -->
        <div class="d-flex" id="phone-menu-wrapper">
            <div id="phone-menu">
                <a href="index.php">
                    <img src="static/images/nla-logo.png" alt="NLA Logo">
                    <h1>National Lottery Authority <br> Key Performance Index</h1>
                </a>
                <ul>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="role">
                        <a href="">Add Role</a>
                    </li>
                </ul>
            </div>
            <div id="alternate-close">

            </div>
        </div>
    </nav>
    <main id='main-body'>
        <section class="container">
            
        </section>
    </main>
    <footer>
        <a href="index.php">
            <img src="static/images//nla-logo.png" alt="NLA Logo">
            <h1>National Lottery Authority Key Performance Index</h1>
        </a>
        <div id="copyright">
        </div>
    </footer>
    <script src="static/js/jquery-3.6.0.min.js"></script>
    <script src="static/js/bootstrap.bundle.min.js"></script>
    <script src="static/js/base.js"></script>
    <script>
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
    </script>
</body>
</html>
