/*
    Sendlio tracking  
*/
sendlio.company = fields.options.sendlio_code

// Creating Basic fields
const basicFields = {
    email: fields.email,
}
if (fields.first_name) basicFields.first_name = fields.first_name
if (fields.last_name) basicFields.last_name = fields.last_name

// Creating properties for wp fields
const wpProps = {}
if (fields.last_login) wpProps.last_login = fields.last_login
if (fields.registered) wpProps.registered = fields.registered
if (fields.display_name) wpProps.display_name = fields.display_name

// Creating properties for woo fields (Removing empty fields)
const wooFields = Object.fromEntries(Object.entries(fields.WooCommerce).filter(([_, value]) => value != null && value !== ''))

// Switching woo phone to basic fields
if (wooFields.hasOwnProperty('phone')) {
    basicFields.phone = wooFields.phone
    delete wooFields.phone
}

const properties = { ...wpProps, ...wooFields }
if (fields.options.isLoggedIn) {
    sendlio.identifyMember({
        ...basicFields,
        properties,
    })
}
