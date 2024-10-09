package dev.alhashim.stock



data class ProductDataClass(
    val id:Int,
    val barcode:String,
    val name:String,
    val description:String,
    val manufacture:String,
    val stock:String,
    val location:String,
    val image:String,
    val warehouse_id:Int,
    val cost_price:String,
    val sale_price:String,
    val created_by_id:Int,
    val created_at:String,
    val category_id:String?,
    val message:String,
    val status: String,
)
