package dev.alhashim.stock

import retrofit2.Call
import retrofit2.http.GET
import retrofit2.http.Query

interface ApiServiceWarehouse {
    @GET("api/get.php?function=warehouses")
    fun getWarehouses(
        @Query("token") token: String
    ):Call<List<WarehouseDataClass>>
}