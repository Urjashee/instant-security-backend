@component('mail::message')
    <div >
        <div class="heading">
            Use this link to sign back in
        </div>
        <hr class="hr-style">
        <div>
            <p class="p-style">
                You can now reset your password and sign back into {{$email}}. This email will work for 2 hours
            </p>
        </div>
        <div>
            <button class="button-style" type="button">
                <a class="a-style" href= '{{$siteName}}/reset-password?token={{$token}}&type={{$type}}'>
                    Get Started</a></button>
        </div>
        <div>
            <p class="p-style">
                If you didn't ask to recover {{$email}} someone may have accidentally entered your email.
                You can safely ignore this message.
            </p>
        </div>
    </div>

@endcomponent
<style>
    @font-face {
        font-family: 'Montserrat';
        src: local("Montserrat");
        src: url('/assets/fonts/Montserrat/Montserrat-Regular.tff') format('tff'),
        url('/assets/fonts/Montserrat.Montserrat-Regular.tff') format('tff');
    }
    @font-face {
        font-family: 'SF-Pro';
        src: local("SF-Pro");
        src: url('/assets/fonts/sf-pro-text-light') format('tff'),
        url('/assets/fonts/sf-pro-text-light') format('tff');
    }
    .heading {
        color: black;
        text-align: center;
        font-family: "SF-Pro", sans-serif;
        font-size: 22px;
        padding: 60px 0 0 0;
    }
    .p-style {
        font-family: "SF-Pro", sans-serif;
        font-size: 13px;
        padding: 10px;
        color: #596676;
    }
    .hr-style {
        border: 1px solid #C6C6C6;
        margin: 20px 0;
    }
    .button-style {
        /* Teal Blue */
        background: #4184f3;
        border-radius: 4px;
        height: 45px;
        width: 150px;
        font-size: 15px;
        font-weight: 600;
        font-family: "SF-Pro", sans-serif;
        border: none;
        margin: 20px 0 20px 40%;
    }
    .a-style {
        text-decoration: none;
        color: #FFF;
    }
</style>
