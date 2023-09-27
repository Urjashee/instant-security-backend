@component('mail::message')
    <div>
        <div class="heading">
            Verify your account.
        </div>
        <div class="background-color">
            <p class="bold">
                Verify your email to authenticate your account
            </p>
            <p>
                To verify your account, click the button below
            </p>
            <button type="button"><a href= '{{$siteName}}/verify?token={{$tokens}}&type={{$type}}' style="text-decoration:none;
        color: white;">
                    Verify Account</a></button>
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
