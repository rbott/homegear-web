samplePowerData = {
    elements: [{
            name: "Weihnachtslicht-1",
            usage: 13.5,
            enabled: true
        },
        {
            name: "Weihnachtslicht-2",
            usage: 28,
            enabled: true
        },
        {
            name: "Heizlüfter",
            usage: 900,
            enabled: true
        }
    ]
}

function getPowerData() {
    return samplePowerData
}

function createPowerElement(item) {
    var fragment = new DocumentFragment()

    div = document.createElement('div')
    classes = ["col-sm-6", "col-md-4", "col-lg-3", "col-xl-2"]
    classes.forEach(item => div.classList.add(item))

    if (item.usage > 500) {
        indicatorClass = "danger"
    } else if (item.usage > 5) {
        indicatorClass = "warning"
    } else {
        indicatorClass = "success"
    }

    box = []
    box.push('<div class="small-box bg-' + indicatorClass + '" id="Power-' + item.name + '">')
    box.push('<div class="inner">')
    box.push('<h3>' + item.usage + 'W</h3>')
    box.push('<p>' + item.name + '</p>')
    box.push('</div>')
    box.push('<div class="icon">')
    box.push('<i class="ion ion-speedometer"></i>')
    box.push('</div>')
    box.push('<a href="# " class="small-box-footer ">More info <i class="fas fa-arrow-circle-right "></i></a>')
    box.push('</div>')
    div.innerHTML = box.join('')

    fragment.appendChild(div)
    return fragment
}


function refreshPowerData() {
    data = getPowerData()
    for (var i = 0; i < data.elements.length; i++) {
        fragment = createPowerElement(data.elements[i])
        powerElement = document.querySelector("#Power-" + data.elements[i].name)
        powerRow = document.querySelector("#humidity-row")
        if (powerElement) {
            powerElement.parentNode.parentNode.replaceChild(fragment.firstChild, powerElement.parentNode)
        } else {
            powerRow.appendChild(fragment)
        }
    }
}