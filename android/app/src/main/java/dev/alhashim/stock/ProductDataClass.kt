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
)
