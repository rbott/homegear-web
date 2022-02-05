function getDeparturesData() {
    const myRequest = new Request('/transportation');

    fetch(myRequest)
        .then(response => response.json())
        .then(data => {
            return data
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
        case 'S-Bahn':
        case 'Zug':
            icon = "ion-android-train"
        case 'Straßenbahn':
        case 'Stadtbahn':
        case 'U-Bahn':
            icon = "ion-android-subway"
        default:
            icon = "ion-android-bus"
    }

    box = []
    box.push('<div class="small-box bg-' + indicatorClass + '" id="Departure-' + item.name + '">')
    box.push('<div class="inner">')
    box.push('<h3>' + item.line + '</h3>')
    box.push('<p>' + item.direction + '</p>')
    box.push('</div>')
    box.push('<div class="icon">')
    box.push('<i class="ion ' + icon + '"></i>')
    box.push('</div>')
    box.push('<a href="#" class="small-box-footer">in X Minuten</a>')
    box.push('</div>')
    div.innerHTML = box.join('')

    fragment.appendChild(div)
    return fragment
}


function refreshDepartureData() {
    data = getDepartureData()
    for (var i = 0; i < data.elements.length; i++) {
        fragment = createDepartureElement(data.elements[i])
        heatingRow = document.querySelector("#departure-row")
        departureElement = document.querySelector("#Departure-" + data.elements[i].name)
        if (departureElement) {
            departureElement.parentNode.parentNode.replaceChild(fragment.firstChild, departureElement.parentNode)
        } else {
            heatingRow.appendChild(fragment)
        }
    }
}