sampleHeatingData = {
    elements: [{
            name: "Wohnzimmer",
            temperature: 20.5,
            valve: 99,
            target: 20.0
        },
        {
            name: "Schlafzimmer",
            temperature: 19.5,
            valve: 15,
            target: 20.0
        },
        {
            name: "Küche",
            temperature: 19.5,
            valve: 50,
            target: 20.0
        },
        {
            name: "Arbeitszimmer",
            temperature: 21.0,
            valve: 95,
            target: 22.0
        },
        {
            name: "Esszimmer",
            temperature: 21.0,
            valve: 89,
            target: 22.0
        }
    ]
}

function getHeaterData() {
    return sampleHeatingData
}

function createHeaterElement(item) {
    var fragment = new DocumentFragment()

    div = document.createElement('div')
    classes = ["col-sm-6", "col-md-4", "col-lg-3", "col-xl-2"]
    classes.forEach(item => div.classList.add(item))

    if (item.valve > 80) {
        indicatorClass = "danger"
    } else if (item.valve > 40) {
        indicatorClass = "warning"
    } else {
        indicatorClass = "info"
    }

    box = []
    box.push('<div class="small-box bg-' + indicatorClass + '" id="Heater-' + item.name + '">')
    box.push('<div class="inner">')
    box.push('<h3>' + item.temperature + '°C</h3>')
    box.push('<p>' + item.name + '</p>')
    box.push('</div>')
    box.push('<div class="icon">')
    box.push('<i class="ion ion-thermometer"></i>')
    box.push('</div>')
    box.push('<a href="#" class="small-box-footer">Ventil: ' + item.valve + '%, Ziel: ' + item.target + '°C</a>')
    box.push('</div>')
    div.innerHTML = box.join('')

    fragment.appendChild(div)
    return fragment
}


function refreshHeaterData() {
    data = getHeaterData()
    for (var i = 0; i < data.elements.length; i++) {
        fragment = createHeaterElement(data.elements[i])
        heatingRow = document.querySelector("#heating-row")
        heaterElement = document.querySelector("#Heater-" + data.elements[i].name)
        if (heaterElement) {
            heaterElement.parentNode.parentNode.replaceChild(fragment.firstChild, heaterElement.parentNode)
        } else {
            heatingRow.appendChild(fragment)
        }
    }
}