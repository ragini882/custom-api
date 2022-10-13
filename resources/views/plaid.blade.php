<html>

<head>

</head>

<body>
    <button id="linkButton">Open Link - Institution Select</button>
</body>
<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
<script>
    var linkHandler = Plaid.create({
        // Make a request to your server to fetch a new link_token.
        token: (await $.post('/create_link_token')).link_token, //'link-development-a00fbe7e-da44-4f8b-b922-fa7598f522cd'
        onLoad: function() {
            // The Link module finished loading.
        },
        onSuccess: function(public_token, metadata) {
            // The onSuccess function is called when the user has
            // successfully authenticated and selected an account to
            // use.
            //
            // When called, you will send the public_token
            // and the selected account ID, metadata.account_id,
            // to your backend app server.
            //
            // sendDataToBackendServer({
            //   public_token: public_token,
            //   account_id: metadata.account_id
            // });
            console.log('Public Token: ' + public_token);
            console.log('Customer-selected account ID: ' + metadata.account_id);
        },
        onExit: function(err, metadata) {
            // The user exited the Link flow.
            if (err != null) {
                // The user encountered a Plaid API error
                // prior to exiting.
                console.log(err);
            }
            // metadata contains information about the institution
            // that the user selected and the most recent
            // API request IDs.
            // Storing this information can be helpful for support.
        },
    });

    // Trigger the authentication view
    document.getElementById('linkButton').onclick = function() {
        linkHandler.open();
    };
</script>

</html>