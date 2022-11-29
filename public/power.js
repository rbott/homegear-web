function getPowerData() {
    loader = document.querySelector("#loader")
    loader.style.display = "inline"
    const myRequest = new Request('/power-sensors');

    fetch(myRequest)
        .then(response => response.json())
        .then(data => {
            for (var i = 0; i < data.elements.length; i++) {
                fragment = createPowerElement(data.elements[i])
                powerElement = document.querySelector("#Power-" + data.elements[i].name)
                powerRow = document.querySelector("#power-row")
                if (powerElement) {
                    powerElement.parentNode.parentNode.replaceChild(fragment.firstChild, powerElement.parentNode)
                } else {
                    powerRow.appendChild(fragment)
                }
            }
            loader = document.querySelector("#loader")
            loader.style.display = "none"
            loaderText = document.querySelector("#loader-info")
            d = new Date()
            loaderText.innerHTML = "Last Update: " + d.toLocaleString()
        })
        .catch(console.error);
}

function createPowerElement(item) {
    var fragment = new DocumentFragment()

    div = document.createElement('div')
    classes = ["col-sm-6", "col-md-4", "col-lg-3", "col-xl-2"]
    classes.forEach(item => div.classList.add(item))

    if (item.unreach) {
        indicatorClass = "black"
        // overwrite usage with 0 if device is unreachable
        item.usage = 0
    }
    else if (item.enabled) {
        if (item.usage > 500) {
            indicatorClass = "danger"
        } else if (item.usage > 5) {
            indicatorClass = "warning"
        } else {
            indicatorClass = "success"
        }
    } else {
        indicatorClass = "gray"
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
    getPowerData()
}
