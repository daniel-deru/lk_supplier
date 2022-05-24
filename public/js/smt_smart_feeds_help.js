
class DynamicRules {
    static rules = []
    constructor(){

        let initialRules = JSON.parse(smart_feed_data.dynamic_rules)


        if(initialRules && initialRules.length > 0) DynamicRules.rules = initialRules

        this.addDynamicListener()
        this.saveListener()
        DynamicRules.createInitialRules()

    }
    // Send the data to php
    saveListener(){
        document.getElementById("ruleset-save-btn").addEventListener('click', function(){
            const data = {
                action: 'get_rules',
                rules: DynamicRules.rules
            }
            jQuery.post(smart_feed_data.ajax_url, data, (response) => {
                console.log(JSON.parse(response))
                alert("Saved")
            })
        })
    }

    // Add the listener for the add button
    addDynamicListener(){
        document.getElementById("add-rule").addEventListener('click', this.dynamicListener)
    }

    // Callback function for the add button event
    dynamicListener(){
        const type = document.getElementById("dynamic_rules")
        const outputContainer = document.getElementById("dynamic-rules-display")
        let rule = null

        DynamicRules.rules.push({more_than: 0, less_than: -Infinity})

        let ruleIndex = DynamicRules.rules.length - 1
    
        rule = DynamicRules.createMarginComponent(ruleIndex)

        outputContainer.appendChild(rule)

    }
    // Put the initial rules in the DOM
    static createInitialRules(){
        let rules = DynamicRules.rules
        const outputContainer = document.getElementById("dynamic-rules-display")
        outputContainer.innerHTML = ""

        for(let i = 0; i < rules.length; i++){
            let rule = null
            rule = DynamicRules.createMarginComponent(i, rules[i]['less_than'], rules[i]['more_than'], rules[i]['margin'])
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
        const deleteBtn = DynamicRules.createDeleteButton(ruleIndex)

        container.appendChild(description)
        container.appendChild(moreComponent)
        container.appendChild(lessComponent)
        container.appendChild(deleteBtn)

        return container
    }

    static createImportStockComponent(ruleIndex, less_than_value=null){
        const container = document.createElement('div')
        container.classList.add("dynamic-rule")

        const description = document.createElement('span')
        description.innerText = "Don't Import if stock: "

        const lessComponent = DynamicRules.createCompareComponent("less_than", ruleIndex, less_than_value)
        const deleteBtn = DynamicRules.createDeleteButton(ruleIndex)

        container.appendChild(description)
        container.appendChild(lessComponent)
        container.appendChild(deleteBtn)

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
        const deleteBtn = DynamicRules.createDeleteButton(ruleIndex)

        container.appendChild(marginContainer)
        container.appendChild(moreComponent)
        container.appendChild(lessComponent)
        container.appendChild(deleteBtn)

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

    static createDeleteButton(index){
        let iconContainer = document.createElement('span')
        iconContainer.classList.add("rule-delete-container")
        iconContainer.dataset.index = index

        iconContainer.addEventListener('click', DynamicRules.deleteRule)

        let icon = document.createElement("i")
        icon.classList.add("fa", "fa-times")
        icon.setAttribute('aria-hidden', 'true')

        iconContainer.appendChild(icon)
        return iconContainer
    }

    static deleteRule(){
        let index = parseInt(this.dataset.index)
        DynamicRules.rules = DynamicRules.rules.filter((r, i) => index !== i )
        DynamicRules.createInitialRules()
    }

    static checkRule(newRule){
        let rules = DynamicRules.rules
        console.log(rules)
        if(!rules) return true
        if(rules.length <= 0) return true

        for(let rule of rules){
            if(rule.type === "import-stock" && newRule.type === "import-stock") return new Error("There can only be one import stock rule.")
        }
    }

//     static checkValidity(){
//         let rules = DynamicRules.rules
        
//         for(let i = 0; i < rules.length - 1; i++){

//             let lessThan = parseFloat(rules[i].less_than)

//             for(let j = i+1; j < rules.length; j++){
//                 let moreThan = parseFloat(rules[j].more_than)
//                 if(lessThan > moreThan) return Error("There is a conflict in the dynamic margin.")
//             }
//         }
//     }
// }
const rules = new DynamicRules()


