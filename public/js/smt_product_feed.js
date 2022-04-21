class ProductTable {
    otherAddListener(){
        const otherCost = document.querySelectorAll(".other-cost input")
        for(let input of otherCost){
            input.addEventListener((e) => this.otherListener(e))
        }
    }
    otherListener(e){
        console.log(e)
    }

}

const product = new ProductTable()
product.otherAddListener()