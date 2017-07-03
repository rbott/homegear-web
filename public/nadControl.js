function nadGet(detail, target) {
    $.getJSON("/nad/" + detail, function(data) {
        if(detail == "source") {
            var output = "";
            switch(data[detail]) {
                case "1":
                    output = "TV";
                    break;
                case "4":
                    output = "Volumio (Spotify/Web Radio)";
                    break;
                case "5":
                    output = "Phono";
                    break;
                case "6":
                    output = "CD";
                    break;
                case "8":
                    output = "Bluetooth";
                    break;
                default:
                    output = data[detail];
            }
        }
        else {
            var output = data[detail]
        }
        $("#" + target).html(output);
    });
}
