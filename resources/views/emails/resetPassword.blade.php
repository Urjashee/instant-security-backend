@component('mail::message')
    <div>
        <div class="heading">
            Forgot your password?
        </div>
        <div class="background-color">
            <p class="bold">
                Forgot your password? It happens to the best of us
            </p>
            <p>
                To reset your password, click the button below. The link will self-destruct after three days
            </p>
            <button type="button"><a href= '{{$siteName}}/reset-password?token={{$token}}&type={{$type}}' style="text-decoration:none;
        color: white;">
                    Reset your password</a></button>
        </div>
    </div>

@endcomponent
<style>
    .heading {
        color: black;
        font-family: "SF-Pro", sans-serif;
        font-size: 18px;
        background-color: white;
    }
    .bold {
        font-weight: bold;
    }
    .background-color {
        padding: 50px 50px;
        background-color: rgba(12, 12, 12);
        color: white;
        text-align: center;
        font-family: Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif;
    }
    div {
        font-family: Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif;
    }
    button {
        /* Teal Blue */
        background: transparent;
        border-radius: 4px;
        height: 45px;
        max-width: fit-content;
        min-width:150px;
        border-color: white;
        font-size: 14px;
        font-weight: 400;
        color: #ffffff;
        font-family: Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif;
        margin: 20px 0 0 0;
    }
</style>
