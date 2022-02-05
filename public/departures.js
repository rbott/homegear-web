function getDepartureData() {
    const myRequest = new Request('/transportation');

    fetch(myRequest)
        .then(response => response.json())
        .then(data => {
            heatingRow = document.querySelector("#departure-row")
            heatingRow.innerHTML = ""
            for (var i = 0; i < data.elements.length; i++) {
                if (i >= 12) {
                    break;
                }
                fragment = createDepartureElement(data.elements[i])
                heatingRow.appendChild(fragment)
            }

        })
        .catch(console.error);
}

function createDepartureElement(item) {
    var fragment = new DocumentFragment()

    div = document.createElement('div')
    classes = ["col-sm-6", "col-md-4", "col-lg-3", "col-xl-2"]
    classes.forEach(item => div.classList.add(item))

    switch (item.type) {
        case 'Bus':
            icon = "ion-android-bus"
            indicatorClass = "indigo"
            break;
        case 'S-Bahn':
        case 'Zug':
            icon = "ion-android-train"
            indicatorClass = "olive"
            break;
        case 'Straßenbahn':
        case 'Stadtbahn':
        case 'U-Bahn':
            icon = "ion-android-subway"
            indicatorClass = "maroon"
            break;
        default:
            icon = "ion-android-bus"
            indicatorClass = "indigo"
    }

    const now = Math.floor(Date.now() / 1000)
    diff_in_minutes = Math.floor((item.time - now) / 60)

    box = []
    box.push('<div class="small-box bg-' + indicatorClass + '" id="Departure-' + item.name + '">')
    box.push('<div class="inner">')
    box.push('<h3>' + item.line + '</h3>')
    box.push('<p>' + item.direction + '</p>')
    box.push('</div>')
    box.push('<div class="icon">')
    box.push('<i class="ion ' + icon + '"></i>')
    box.push('</div>')
    box.push('<a href="#" class="small-box-footer">in ' + diff_in_minutes + ' Minuten</a>')
    box.push('</div>')
    div.innerHTML = box.join('')

    fragment.appendChild(div)
    return fragment
}


function refreshDepartureData() {
    getDepartureData()
}