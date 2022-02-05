sampleHumidityData = {
    elements: [{
            name: "Wohnzimmer",
            temperature: 20.5,
            humidity: 55
        },
        {
            name: "Schlafzimmer",
            temperature: 19.5,
            humidity: 63
        },
        {
            name: "Bad",
            temperature: 19.5,
            humidity: 56
        }
    ]
}

function getHumidityData() {
    return sampleHumidityData
}

function createHumidityElement(item) {
    var fragment = new DocumentFragment()

    div = document.createElement('div')
    classes = ["col-sm-6", "col-md-4", "col-lg-3", "col-xl-2"]
    classes.forEach(item => div.classList.add(item))

    if (item.humidity > 70) {
        indicatorClass = "danger"
    } else if (item.humidity > 60) {
        indicatorClass = "warning"
    } else if (item.humidity > 50) {
        indicatorClass = "success"
    } else {
        indicatorClass = "warning"
    }

    box = []
    box.push('<div class="small-box bg-' + indicatorClass + '" id="Humidity-' + item.name + '">')
    box.push('<div class="inner">')
    box.push('<h3>' + item.temperature + '°C</h3>')
    box.push('<p>' + item.name + '</p>')
    box.push('</div>')
    box.push('<div class="icon">')
    box.push('<i class="ion ion-waterdrop"></i>')
    box.push('</div>')
    box.push('<a href="# " class="small-box-footer ">More info <i class="fas fa-arrow-circle-right "></i></a>')
    box.push('</div>')
    div.innerHTML = box.join('')

    fragment.appendChild(div)
    return fragment
}


function refreshHumidityData() {
    data = getHumidityData()
    for (var i = 0; i < data.elements.length; i++) {
        fragment = createHumidityElement(data.elements[i])
        humidityElement = document.querySelector("#Humidity-" + data.elements[i].name)
        humidityRow = document.querySelector("#humidity-row")
        if (humidityElement) {
            humidityElement.parentNode.parentNode.replaceChild(fragment.firstChild, humidityElement.parentNode)
        } else {
            humidityRow.appendChild(fragment)
        }
    }
}