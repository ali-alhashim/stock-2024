package dev.alhashim.stock
import retrofit2.Call
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST



interface ApiServiceLogin {

    @FormUrlEncoded
    @POST("api/post.php")
    fun login(
        @Field("username") username: String,
        @Field("password") password: String,
        @Field("function") function:String, //login
        @Field("device") device:String, // android
    ): Call<LoginDataClass>


}