const wooCommerceHelpBtn = document.getElementById("woocommerce-api")


wooCommerceHelpBtn.addEventListener("click", () => {
    console.log("Hello")
    const wooCommerceInstructions = document.getElementById("woocommerce-help")
    wooCommerceInstructions.classList.toggle("show")
})