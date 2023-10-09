{{--@component('mail::message')--}}
<div class="card">
    <img class="headerImage" alt="image" src="https://instant-security.s3.amazonaws.com/instant_security_header.png">
    <div>
        <h2>
            Hello {{ $email }},
        </h2>
        <p class="p1">
            Forgot your password? It happens to the best of us. To reset your password, click the button below. The link
            will self-destruct after 24 hours.
        </p>
        <button type="button">
            <a href='{{$siteName}}reset-password?token={{$token}}&type={{$type}}&role_id={{$roleId}}' style="text-decoration:none;color: white;">
                Reset Password</a></button>
    </div>
</div>

{{--@endcomponent--}}
<style>
    /*@font-face {*/
    /*    font-family: "work-sans-regular";*/
    /*    src: url('public/fonts/Work_Sans/WorkSans-Regular.ttf');*/
    /*    font-weight: normal;*/
    /*    font-style: normal;*/
    /*}*/
    .headerImage {
        width: 100%;
        /*height: 120px*/
    }

    h2 {
        color: #0c0c0c;
        font-family: "Google Sans", sans-serif !important;
    }

    div {
        margin: 70px 60px 70px 60px;
        font-family: "Google Sans", sans-serif !important;
    }

    hr {
        margin: 20px 0 0 0;
        background: #DCDCE0;
        height: 2px;
        border-color: #DCDCE0;
    }

    .p1 {
        font-family: "Google Sans", sans-serif !important;
        color: #0c0c0c;
        font-size: 14px;
        font-weight: 400;
        line-height: 25px;
        text-align: left;
        margin: 20px 0 0 0;
    }

    .p2 {
        font-family: "Google Sans", sans-serif !important;
        color: #0c0c0c;
        font-size: 14px;
        font-weight: 400;
        line-height: 23px;
        text-align: left;
        margin: 20px 0 0 0;
    }

    .spanDetails {
        margin: 50px 0 0 0;
        font-family: "Google Sans", sans-serif !important;
        color: #0c0c0c;
        font-size: 14px;
        font-weight: 400;
        line-height: 23px;
        text-align: left;
    }

    button {
        /* Teal Blue */
        background: #001F64;
        height: 45px;
        width: 350px;
        border-color: transparent;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
        font-family: "Google Sans", sans-serif !important;
        margin: 60px 0 30px 0;
        border-radius: 10px;
    }
    .card {
        /* Add shadows to create the "card" effect */
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }

    /* On mouse-over, add a deeper shadow */
    .card:hover {
        box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    }
</style>
