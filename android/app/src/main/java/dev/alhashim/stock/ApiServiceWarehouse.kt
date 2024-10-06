package dev.alhashim.stock

import retrofit2.Call
import retrofit2.http.GET

interface ApiServiceWarehouse {
    @GET("api/get.php?function=warehouses")
    fun getWarehouses():Call<List<WarehouseDataClass>>
}