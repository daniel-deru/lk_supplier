
let initial = [
    {
        "type": "import-price",
        "less_than": 100,
        "more_than": 200
    },
    {
        "type": "import-stock",
        "less_than": 300
    },
    {
        "type": "margin",
        "margin": 20,
        "less_than": 100,
        "more_than": 300
    }
]
class DynamicRules {
    static rules = initial
    constructor(){
        this.addDynamicListener()
        this.saveListener()
        this.createInitialRules()
    }

    saveListener(){
        document.getElementById("settings-save-btn").addEventListener('click', function(){
            const data = {
                action: 'get_rules',
                rules: DynamicRules.rules
            }
            jQuery.post(smart_feed_data.ajax_url, data, (response) => {
                console.log(JSON.parse(response))
            })
        })
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

        console.log(DynamicRules.rules)
        outputContainer.appendChild(rule)

    }

    createInitialRules(){
        let rules = DynamicRules.rules
        const outputContainer = document.getElementById("dynamic-rules-display")

        for(let i = 0; i < rules.length; i++){
            let rule = null
            switch(rules[i]['type']){
                case "import-price":  
                    rule = DynamicRules.createImportPriceComponent(i, rules[i]['less_than'], rules[i]['more_than'])
                    break
                case "import-stock":
                    rule = DynamicRules.createImportStockComponent(i, rules[i]['less_than'])
                    break
                case "margin":
                    rule = DynamicRules.createMarginComponent(i, rules[i]['less_than'], rules[i]['more_than'], rules[i]['margin'])
                    break
            }
            outputContainer.appendChild(rule)
        }
    }

    static createImportPriceComponent(ruleIndex, less_than_value=null, more_than_value=null){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const description = document.createElement('span')
        description.innerText = "Don't import if price is: "
        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex, less_than_value)
        const moreComponent = DynamicRules.createCompareComponent("more_than", ruleIndex, more_than_value)

        container.appendChild(description)
        container.appendChild(lessComponent)
        container.appendChild(moreComponent)

        console.log(container)

        return container
    }

    static createImportStockComponent(ruleIndex, less_than_value=null){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const description = document.createElement('span')
        description.innerText = "Don't Import if stock: "

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex, less_than_value)

        container.appendChild(description)
        container.appendChild(lessComponent)

        console.log(container)

        return container
    }

    static createMarginComponent(ruleIndex, less_than_value=null, more_than_value=null, margin_value=null){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const marginContainer = document.createElement('span')
        marginContainer.innerText = "Set Margin as: "

        const margin = document.createElement('input')
        if(margin_value) margin.value = margin_value
        margin.dataset.index = ruleIndex
        margin.type = 'number'
        
        margin.addEventListener('input', DynamicRules.marginListner)

        marginContainer.appendChild(margin)

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex, less_than_value)
        const moreComponent = DynamicRules.createCompareComponent("more_than", ruleIndex, more_than_value)

        container.appendChild(marginContainer)
        container.appendChild(lessComponent)
        container.appendChild(moreComponent)

        return container
    }

    static createCompareComponent(type, ruleIndex, value=null){
        let text = type === "more_than" ? " More Than: " : " Less than: "

        const container = document.createElement('span')
        const input = document.createElement('input')

        input.addEventListener('input', DynamicRules.ruleSetListener)

        if(value) input.value = value
        input.type = "number"
        input.classList.add(type)
        input.dataset.type = type
        input.dataset.index = ruleIndex

        container.innerText = text

        container.appendChild(input)

        return container
    }

    static ruleSetListener(){
        let ruleIndex = parseInt(this.dataset.index)
        DynamicRules.rules[ruleIndex][this.dataset.type] = parseFloat(this.value)
    }

    static marginListner(){
        let ruleIndex = parseInt(this.dataset.index)
        DynamicRules.rules[ruleIndex]['margin'] = parseFloat(this.value)
    }
}

const rules = new DynamicRules()
console.log(smart_feed_data)

