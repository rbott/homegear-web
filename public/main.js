document.addEventListener("DOMContentLoaded", function(event) {
    refreshHumidityData()
    setInterval(refreshHumidityData, 60000)

    refreshPowerData()
    setInterval(refreshPowerData, 60000)

    refreshHeaterData()
    setInterval(refreshHeaterData, 60000)

    refreshDepartureData()
    setInterval(refreshDepartureData, 60000)
});
