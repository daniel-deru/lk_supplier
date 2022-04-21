class ProductTable {
    constructor(){
        this.prices = this.getCostPrices()
        // This is a nodelist of the element that has the data
        this.costOfGoods = document.querySelectorAll(".cost-of-goods")
        this.sellingPrice = document.querySelectorAll(".price")
        this.profit = document.querySelectorAll(".profit")
        this.setPrices()

    }

    setPrices(){
        for(let i = 0; i< this.costOfGoods.length; i++){
            this.costOfGoods[i].innerHTML = this.prices[i]
            this.sellingPrice[i].innerText = this.prices[i]
            this.profit[i].innerText = 0
        }
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
            markupInput[i].addEventListener('change', this.markupListener)
        }
    }


    getCostPrices(){
        return Array.from(document.querySelectorAll(".cost-price")).map(div => parseFloat(div.innerText))        
    }

    markupListener(){
        const markupType = document.querySelector(`#markup-type${this.dataset.index}`)
        

    }


    otherListener(){
        const otherCostRegex = /[0-9]{1,5}(\.[0-9]{1,3})?/
        const markupRegex = /[0-9]{1,9}(\.[0-9]{1,3})?/


        // Input elements
        let otherCost = this.value
        let cost = parseFloat(document.querySelector(`#cost-price${this.dataset.index}`).innerText)
        let markupType = document.querySelector(`#markup-type${this.dataset.index}`).value
        let markup = document.querySelector(`#markup${this.dataset.index}`).value
        // check if the markup isset and give a default value of 0
        markup = markupRegex.test(markup) ? parseInt(markup) : 0

        // Output elements
        let costOfGoods = document.querySelector(`#cost-of-goods${this.dataset.index}`)
        let sellingPrice = document.querySelector(`#price${this.dataset.index}`)
        let profitValue = document.querySelector(`#price${this.dataset.index}`)


        if(otherCostRegex.test(otherCost)){

            let costOfGoodsValue = cost + parseFloat(otherCost)

            costOfGoods.innerText = costOfGoodsValue

            // calculate the selling price excluding
            let sellingPriceExcl = markupType === "percent" ? costOfGoodsValue * (100 + markup) / 100 : costOfGoodsValue + markup


            // create vars for the output data
            let sellingPriceIncl = sellingPriceExcl * 1.15
            let profit = sellingPriceExcl - costOfGoodsValue

            console.log(markupRegex.test(markup))
            console.log(markupType)
            console.log(`sellingPriceExcl: ${sellingPriceExcl} = cost: ${costOfGoodsValue} * (100 + ${markup}) / 100`)
            console.log(sellingPriceIncl)

            // set the selling price and profit
            sellingPrice.innerText = sellingPriceIncl
            profitValue.innerText = profit
        }
        
    }


}
// event => ProductTable.#otherListener(event, i, this.prices[i], this.costOfGoods[i])

const product = new ProductTable()
product.otherAddListener()
