let products = rectron_products.products

class ProductTable {
    constructor(){
        this.addListeners()
        this.setCostOfGoods()
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
        // console.log(finalForm)

        const data = {
            action: 'smt_smart_feeds_get_custom_product_data',
            data: finalForm
        }

        jQuery.post(rectron_products.ajax_url, data, (response) => {
            console.log(response)
            alert("Saved")
        })
    }

    // This gets the "do not import" checkboxes for when the save button is clicked
    static getImportCheckBoxes(productObject){
        const import_chcckboxes = document.querySelectorAll(".import")

        for(let checkbox of import_chcckboxes){
            const sku = checkbox.dataset.sku
            if(checkbox.checked && products[sku]['status'] === 'publish'){
                if(sku in productObject) productObject[sku] = {...productObject[sku], skip: 1}
                else productObject[sku] = { skip: 1 } 
            }
            else if(!checkbox.checked && products[sku]['status'] !== 'publish'){
                if(sku in productObject) productObject[sku] = {...productObject[sku], skip: 0}
                else productObject[sku] = { skip: 0 }
            }
        }

        return productObject
    }

    // This gets the other cost input for when the save button is clicked
    static getOtherCost(productObject){
        const otherCostInputs = document.querySelectorAll(".other-cost input")
        const numberRegex = /[0-9]*(\.[0-9]*)?/
        for(let input of otherCostInputs){

            const sku = input.dataset.sku

            if(input.value === '' && products[sku]['custom_data']['other_cost'] == '0') continue

            if(input.value != products[sku]['custom_data']['other_cost'] && numberRegex.test(input.value)){

                let otherCost = input.value ? parseFloat(input.value) : 0

                if(sku in productObject) productObject[sku] = {...productObject[sku], otherCost }
                else productObject[sku] = { otherCost }
            }
        }

        return productObject
    }

    // This gets the markup input for when the save button is clicked
    static getMarkup(productObject){
        const markupInputs = document.querySelectorAll(".markup input")

        for(let input of markupInputs){

            const sku = input.dataset.sku
            const markupType = document.getElementById(`markup-type${input.dataset.index}`)
            const currentMarkup = parseFloat(products[sku]['custom_data']['margin'])
            if(parseFloat(input.value) != parseFloat(currentMarkup)){
                // console.log(input.value)
                let markup = parseFloat(input.value)
                if(sku in productObject) productObject[sku] = {...productObject[sku], markup, markupType: markupType.value }
                else productObject[sku] = { markup, markupType: markupType.value }
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
        const costOfGoods = parseFloat(document.querySelector(`#cost-of-goods${this.dataset.index}`).innerText.replace(/\,+/g, ""))
        let sellingPrice = document.querySelector(`#price${this.dataset.index}`)
        let profitValue = document.querySelector(`#profit${this.dataset.index}`)

        
        if(markupRegex.test(this.value)){
            // calculate markup
            const markup = markupType.value === 'percent'? costOfGoods * (parseFloat(this.value) / 100) : parseFloat(this.value)

            // calculate selling prices
            const sellingPriceExcl = costOfGoods + markup
            const sellingPriceIncl = sellingPriceExcl * 1.15

            // set the selling price and profit
            sellingPrice.innerText = Math.floor(Math.round((sellingPriceIncl + Number.EPSILON) * 1_000_000) / 1_000_000) + .9
            profitValue.innerText = Math.round((markup + Number.EPSILON) * 1_000_000) / 1_000_000
        }

        
    }

    setCostOfGoods(){
        const costOfGoods = document.querySelectorAll(".cost-of-goods")
        for(let node of costOfGoods){
            const sku = node.dataset.sku
            const cost = parseFloat(products[sku]['custom_data']['cost'])
            const other_cost = parseFloat(products[sku]['custom_data']['other_cost'])
            node.innerText = cost + other_cost
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

        // for(let i = 0; i < products.length; i++){
        //     if(!(descRegex.test(products[i].Code) || descRegex.test(products[i].Title))) exclude.push(i)
        // }
        for(let product in products){
            if(!(descRegex.test(products[product]['sku']) || descRegex.test(products[product]['name']))) exclude.push(i)
        }

        return elementList
                    .map((el, index) => exclude.includes(index) ? null : el)
                    .filter(el => el !== null)   
    }

    static filterPrice(comparison, comparePrice, elementList){
        let exclude = []
        let priceRegex = /[0-9]{1,9}(\.[0-9]{1,3})?/

        if(priceRegex.test(comparePrice)){
            // for(let i = 0; i < products.length; i++){
            //     if(comparison === "more_than" && parseFloat(products[i].SellingPrice) <= parseFloat(comparePrice)) exclude.push(i)
            //     else if(comparison === "less_than" && parseFloat(products[i].SellingPrice) >= parseFloat(comparePrice)) exclude.push(i)
            // }

            for(let product in product){
                if(comparison === "more_than" && parseFloat(products[product]['price']) <= parseFloat(comparePrice)) exclude.push(i)
                else if(comparison === "less_than" && parseFloat(products[product]['price']) >= parseFloat(comparePrice)) exclude.push(i)
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

console.log(rectron_products)
