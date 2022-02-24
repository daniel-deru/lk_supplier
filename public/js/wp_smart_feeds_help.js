console.log("hello world")


const interval = document.getElementById("interval")

console.log("This is the data from the hidden input", interval)
console.log(interval.value)

const radio = document.getElementById(interval.value)
console.log(radio)

radio.checked = true