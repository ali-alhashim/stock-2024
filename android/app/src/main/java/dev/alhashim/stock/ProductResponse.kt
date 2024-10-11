package dev.alhashim.stock

data class ProductResponse(
    val status: String, // to capture the "status" field
    val products: List<ProductDataClass> // to capture the list of products
)
