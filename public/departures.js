function getDeparturesData() {
    // fetch-Aufruf mit Pfad zur XML-Datei
    fetch('https://openservice-test.vrr.de/static02/XML_DM_REQUEST?sessionID=0&requestID=0&language=DE&type_dm=stopID&name_dm=20020105')
        .then(function(response) {
            return response.text();
        })
        .then(function(data) {
            console.log(data);

            let parser = new DOMParser(),
                xmlDoc = parser.parseFromString(data, 'text/xml');


        }).catch(function(error) {
            console.log("Fehler: bei Auslesen der XML-Datei " + error);
        });
}