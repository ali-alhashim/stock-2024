package dev.alhashim.stock

import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Call
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part

interface ApiServiceAddProduct {

    @Multipart
    @POST("api/post.php")
    fun addProduct(
        @Part("username") username: RequestBody,   //username
        @Part("function") function: RequestBody,   //addProduct
        @Part("device") device: RequestBody,       //android
        @Part("token") token: RequestBody,         //token
        @Part("name") name: RequestBody,           //product name
        @Part image: MultipartBody.Part,            //product image
        @Part("newStock") newStock:RequestBody,
        @Part("description")description:RequestBody, //description
        @Part("manufacture")manufacture:RequestBody, // manufacture
        @Part("location")location:RequestBody, //location
        @Part("warehouse_id")warehouse_id:RequestBody, //
        @Part("barcode")barcode:RequestBody
    ): Call<AddProductDataClass>
}
