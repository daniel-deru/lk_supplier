

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

        if(type.value){
            DynamicRules.rules.push({ "type": type.value })
        }
    }
}

const rules = new DynamicRules()

