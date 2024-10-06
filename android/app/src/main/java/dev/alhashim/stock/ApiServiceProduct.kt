package dev.alhashim.stock

import retrofit2.Call
import retrofit2.http.Query
import retrofit2.http.GET

interface ApiServiceProduct {

    @GET("api/get.php?function=getProductByBarcode")
    fun getProduct(
        @Query("barcode") barcode: String,
        @Query("token") token: String
    ):Call<List<ProductDataClass>>
}