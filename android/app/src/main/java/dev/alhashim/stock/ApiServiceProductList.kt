package dev.alhashim.stock

import retrofit2.Call
import retrofit2.http.GET
import retrofit2.http.Query

interface ApiServiceProductList {
    @GET("api/get.php?function=getProductList")
    fun getProductList(
        @Query("token") token: String,
        @Query("page") page: Int
    ): Call<ProductResponse>
}