package dev.alhashim.stock

import retrofit2.Call
import retrofit2.http.Field
import retrofit2.http.GET

interface ApiServiceProduct {
    @GET("api/get.php?function=getProductByBarcode")
    fun getProduct(
        @Field("barcode") barcode: String,
        @Field("token")token:String
    ):Call<List<ProductDataClass>>
}