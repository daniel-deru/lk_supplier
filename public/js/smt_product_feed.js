let products = rectron_products.products

class ProductTable {
    constructor(){
        this.addListeners()
    }



    addListeners(){
        this.otherAddListener()
        this.markupAddListener()
        this.saveAddListner()
    }

    saveAddListner(){
        document.getElementById("save-settings").addEventListener('click', this.saveClicked)
    }


    otherAddListener(){
        const otherCostInputs = document.querySelectorAll(".other-cost input")
        for(let i = 0; i < otherCostInputs.length; i++){
            otherCostInputs[i].addEventListener('input', this.otherListener)
        }
    }

    markupAddListener(){
        const markupInput = document.querySelectorAll(".markup input")
        for(let i = 0; i < markupInput.length; i++){
            markupInput[i].addEventListener('input', this.markupListener)
        }
    }

    saveClicked(){
        let ajax = {}
        let checkboxObject = ProductTable.getImportCheckBoxes(ajax)
        let otherCostObject = ProductTable.getOtherCost(checkboxObject)
        let finalForm = ProductTable.getMarkup(otherCostObject)
        console.log(finalForm)
    }

    // This gets the "do not import" checkboxes for when the save button is clicked
    static getImportCheckBoxes(productObject){
        const import_chcckboxes = document.querySelectorAll(".import")

        for(let checkbox of import_chcckboxes){
            if(checkbox.checked){
                const sku = checkbox.dataset.sku
                if(sku in productObject) productObject[sku] = {...productObject[sku], skip: true}
                else productObject[sku] = { skip: true } 
            } 
        }

        return productObject
    }

    // This gets the other cost input for when the save button is clicked
    static getOtherCost(productObject){
        const otherCostInputs = document.querySelectorAll(".other-cost input")

        for(let input of otherCostInputs){
            if(input.value){
                const sku = input.dataset.sku
                if(sku in productObject) productObject[sku] = {...productObject[sku], otherCost: parseFloat(input.value) }
                else productObject[sku] = { otherCost: parseFloat(input.value) }
            } 
        }

        return productObject
    }

    // This gets the markup input for when the save button is clicked
    static getMarkup(productObject){
        const markupInputs = document.querySelectorAll(".markup input")

        for(let input of markupInputs){
            if(input.value){
                const sku = input.dataset.sku
                const markupType = document.getElementById(`markup-type${input.dataset.index}`)

                if(sku in productObject) productObject[sku] = {...productObject[sku], markup: parseFloat(input.value), markupType: markupType.value }
                else productObject[sku] = { markup: parseFloat(input.value), markupType: markupType.value }
            } 
        }

        return productObject
    }

    otherListener(){
        const otherCostRegex = /[0-9]{1,5}(\.[0-9]{1,3})?/
        const markupRegex = /[0-9]{1,9}(\.[0-9]{1,3})?/

        // Input elements
        let otherCost = this.value
        let cost = document.querySelector(`#cost-price${this.dataset.index}`).innerText
        let markupType = document.querySelector(`#markup-type${this.dataset.index}`).value
        let markup = document.querySelector(`#markup${this.dataset.index}`).value
        // check if the markup isset and give a default value of 0
        markup = markupRegex.test(markup) ? parseInt(markup) : 0
        cost = parseFloat(cost.replace(",", ""))
        console.log(cost)
        // Output elements
        let costOfGoods = document.querySelector(`#cost-of-goods${this.dataset.index}`)
        let sellingPrice = document.querySelector(`#price${this.dataset.index}`)
        let profitValue = document.querySelector(`#profit${this.dataset.index}`)


        if(otherCostRegex.test(otherCost)){

            // cost + other cost = cost of goods
            let costOfGoodsAmount = cost + parseFloat(otherCost)

            // calculate the selling price excluding
            let sellingPriceExcl = markupType === "percent" ? costOfGoodsAmount * (100 + markup) / 100 : costOfGoodsAmount + markup

            // create vars for the output data
            let sellingPriceIncl = sellingPriceExcl * 1.15
            let profit = sellingPriceExcl - costOfGoodsAmount


            // set the cost of goods and selling price and profit
            costOfGoods.innerText = costOfGoodsAmount.toFixed(2)
            sellingPrice.innerText = sellingPriceIncl.toFixed(2)
            profitValue.innerText = profit
        }
        
    }

    markupListener(){

        const markupRegex = /[0-9]{1,9}(\.[0-9]{1,3})?/
        
        // grab the elements that you need
        const markupType = document.querySelector(`#markup-type${this.dataset.index}`)
        const costOfGoods = document.querySelector(`#cost-of-goods${this.dataset.index}`)
        let sellingPrice = document.querySelector(`#price${this.dataset.index}`)
        let profitValue = document.querySelector(`#profit${this.dataset.index}`)

        
        if(markupRegex.test(this.value)){
            // calculate markup
            const markup = markupType.value === 'percent'? parseFloat(costOfGoods.innerText) * (parseFloat(this.value) / 100) : parseFloat(this.value)

            // calculate selling prices
            const sellingPriceExcl = parseFloat(costOfGoods.innerText) + markup
            const sellingPriceIncl = sellingPriceExcl * 1.15  

            // set the selling price and profit
            sellingPrice.innerText = Math.round((sellingPriceIncl + Number.EPSILON) * 1_000_000) / 1_000_000
            profitValue.innerText = Math.round((markup + Number.EPSILON) * 1_000_000) / 1_000_000
        }

        
    }


}


// Add two event listeners to fire everytime there is an input
class FilterFeed {
    constructor(){
        this.addPriceListener()
        this.addDescListener()
        this.reset()
    }

    reset(){
        document.getElementById("reset-filter").addEventListener("click", event => {

            event.preventDefault()
            document.getElementById("filter-description").value = ""
            document.getElementById("price-filter").value = ""
            Array.from(document.querySelectorAll(".smt-body")).forEach(el => el.classList.remove("filtered"))

        })
    }

    addPriceListener(){
        document.getElementById("price-filter").addEventListener('input', this.priceListener)
    }

    addDescListener(){
        document.getElementById("filter-description").addEventListener("input", this.descListener)
    }

    priceListener(){
        let comparePrice = parseFloat(this.value)
        let comparison = document.getElementById("price-filter-compare").value
        let rows = Array.from(document.querySelectorAll(".smt-body"))
        let desc = document.getElementById("filter-description").value

        rows.forEach(el => el.classList.remove("filtered"))

        if(desc) rows = FilterFeed.filterDescription(desc, rows)
        rows = FilterFeed.filterPrice(comparison, comparePrice, rows)

        rows.forEach(el => el.classList.add("filtered"))        
    }

    descListener(){
        // Get the necessary data 
        let rows = Array.from(document.querySelectorAll(".smt-body"))
        let comparePrice = parseFloat(document.getElementById("price-filter").value)
        let comparison = document.getElementById("price-filter-compare").value
        let description = this.value

        // Remove previous filtered class
        rows.forEach(el => el.classList.remove("filtered"))

        // Use statc methods to process filtering
        if(comparePrice) rows = FilterFeed.filterPrice(comparison, comparePrice, rows)
        rows = FilterFeed.filterDescription(description, rows)

        // Add style to the filtered Items
        rows.forEach(el => el.classList.add("filtered"))
        
    }

    static filterDescription(description, elementList){
        let exclude = []
        const descRegex = new RegExp(`${description}`, "gi")

        for(let i = 0; i < products.length; i++){
            if(!(descRegex.test(products[i].Code) || descRegex.test(products[i].Title))) exclude.push(i)
        }

        return elementList
                    .map((el, index) => exclude.includes(index) ? null : el)
                    .filter(el => el !== null)   
    }

    static filterPrice(comparison, comparePrice, elementList){
        let exclude = []
        let priceRegex = /[0-9]{1,9}(\.[0-9]{1,3})?/

        if(priceRegex.test(comparePrice)){
            for(let i = 0; i < products.length; i++){
                if(comparison === "more_than" && parseFloat(products[i].SellingPrice) <= parseFloat(comparePrice)) exclude.push(i)
                else if(comparison === "less_than" && parseFloat(products[i].SellingPrice) >= parseFloat(comparePrice)) exclude.push(i)
            }
        }

        return elementList
                    .map((el, index) => exclude.includes(index) ? null : el)
                    .filter(el => el !== null) 
    }

    getProducts(){
        return document.querySelectorAll(".smt-body")
        // console.log(products)
    }
}

// const cost = document.querySelectorAll(".cost-price")
// for(let el of cost){
//     console.log(parseFloat(el.innerText))
// }
const product = new ProductTable()
const filter = new FilterFeed()

