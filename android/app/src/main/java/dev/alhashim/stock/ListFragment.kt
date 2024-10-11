package dev.alhashim.stock

import android.content.ContentValues.TAG
import android.content.Context
import android.content.SharedPreferences
import android.os.Bundle
import android.util.Log
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.google.gson.GsonBuilder
import okhttp3.OkHttpClient
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory


class ListFragment(private val page: Int = 0) : Fragment() {

    private lateinit var productRecyclerView: RecyclerView
    private lateinit var preferences: SharedPreferences
    private lateinit var savedServerURL: String
    private lateinit var token: String
    private val TAG = "ListFragment"

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.fragment_list, container, false)

        // Initialize RecyclerView
        productRecyclerView = view.findViewById(R.id.recyclerViewProductList)

        // Set an empty adapter initially
        productRecyclerView.adapter = ProductRecyclerAdapter(requireActivity(),emptyList())

        // Set a LinearLayoutManager (this can be done here even without data)
        productRecyclerView.layoutManager = LinearLayoutManager(requireContext())

        // Get SharedPreferences data (token, server URL)
        preferences = requireActivity().getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        savedServerURL = preferences.getString("server", "").toString()
        token = preferences.getString("token", "").toString()

        // Load the product list from the server
        loadProductList(page)

        return view
    }

    private fun loadProductList(page: Int = 0) {
        val gson = GsonBuilder().setLenient().create()
        val okHttpClient = OkHttpClient.Builder()
            .cookieJar(CookieManager.cookieJar)
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl(savedServerURL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()

        val apiService = retrofit.create(ApiServiceProductList::class.java)

        // Make API call to get the product list
        val call = apiService.getProductList(token, page)

        call.enqueue(object : Callback<ProductResponse> {
            override fun onResponse(
                call: Call<ProductResponse>,
                response: Response<ProductResponse>
            ) {
                if (response.isSuccessful) {
                    Log.i(TAG, "onResponse => Successful for get product list")
                    val productResponse = response.body()
                    if (productResponse != null && productResponse.products.isNotEmpty()) {
                        Log.i(TAG, "Product list size: ${productResponse.products.size}")
                        updateRecyclerView(productResponse.products)
                    } else {
                        Log.i(TAG, "Product list is empty.")
                        showEmptyListMessage()
                    }
                } else {
                    Log.e(TAG, "onResponse => Failed with status code: ${response.code()}")
                    handleErrorResponse(response.code())
                }
            }

            override fun onFailure(call: Call<ProductResponse>, t: Throwable) {
                Log.e(TAG, "Network error: ${t.message}")
                handleNetworkError(t)
            }
        })

    }

    private fun updateRecyclerView(productList: List<ProductDataClass>) {
        // Update the adapter with the new product list
        val adapter = ProductRecyclerAdapter(requireContext(), productList)
        productRecyclerView.adapter = adapter
    }

    private fun showEmptyListMessage() {
        // You can show a TextView with a "No Products Available" message or something similar
        Log.i(TAG, "Displaying empty list message.")
    }

    private fun handleErrorResponse(code: Int) {
        // Handle different HTTP status codes (e.g., 401 Unauthorized, 500 Internal Server Error)
        when (code) {
            401 -> {
                // Token invalid, maybe prompt user to login again
                Log.e(TAG, "Authentication error. Please log in again.")
            }
            else -> {
                Log.e(TAG, "Server returned error code: $code")
            }
        }
    }

    private fun handleNetworkError(t: Throwable) {
        // Handle network errors (no connection, timeout, etc.)
        Log.e(TAG, "Network error: ${t.message}")
    }
}

