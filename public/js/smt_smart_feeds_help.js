

class DynamicRules {
    static rules = []
    constructor(){
        this.addDynamicListener()
    }

    addDynamicListener(){
        document.getElementById("add-rule").addEventListener('click', this.dynamicListener)
    }

    dynamicListener(){
        const type = document.getElementById("dynamic_rules")
        const outputContainer = document.getElementById("dynamic-rules-display")
        let rule = null

        if(type.value){
            DynamicRules.rules.push({ "type": type.value })
        }

        let ruleIndex = DynamicRules.rules.length - 1

        
        switch(type.value){
            case "import-price":  
                rule = DynamicRules.createImportPriceComponent(ruleIndex)
                break
            case "import-stock":
                rule = DynamicRules.createImportStockComponent(ruleIndex)
                break
            case "margin":
                rule = DynamicRules.createMarginComponent(ruleIndex)
                break
        }

        console.log(rule)
        console.log(DynamicRules.rules)
        outputContainer.appendChild(rule)

    }

    static createImportPriceComponent(ruleIndex){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const description = document.createElement('span')
        description.innerText = "Don't import if price is: "

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex)
        const moreComponent = DynamicRules.createCompareComponent("more_than", ruleIndex)

        container.appendChild(description)
        container.appendChild(lessComponent)
        container.appendChild(moreComponent)

        console.log(container)

        return container
    }

    static createImportStockComponent(ruleIndex){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const description = document.createElement('span')
        description.innerText = "Don't Import if stock: "

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex)

        container.appendChild(description)
        container.appendChild(lessComponent)

        console.log(container)

        return container
    }

    static createMarginComponent(ruleIndex){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const marginContainer = document.createElement('span')
        marginContainer.innerText = "Set Margin as: "

        const margin = document.createElement('input')
        margin.type = 'number'

        marginContainer.appendChild(margin)

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex)
        const moreComponent = DynamicRules.createCompareComponent("more_than", ruleIndex)

        container.appendChild(marginContainer)
        container.appendChild(lessComponent)
        container.appendChild(moreComponent)

        return container
    }

    static createCompareComponent(type, ruleIndex){
        let text = type === "more_than" ? " More Than: " : " Less than: "

        const container = document.createElement('span')
        const input = document.createElement('input')

        input.type = "number"
        input.classList.add(type)
        container.innerText = text
        container.dataset.type = type
        container.dataset.index = ruleIndex

        container.appendChild(input)



        return container
    }
}

const rules = new DynamicRules()

