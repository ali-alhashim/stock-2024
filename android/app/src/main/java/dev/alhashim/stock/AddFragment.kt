package dev.alhashim.stock

import android.content.ContentValues.TAG
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.ProgressBar
import android.widget.Spinner
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import com.bumptech.glide.Glide
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

class AddFragment : Fragment() {

    private lateinit var warehousesList: MutableList<String>
    private lateinit var editTextBarcode: EditText
    private  lateinit var editTextName:EditText
    private lateinit var editTextManufacture:EditText
    private lateinit var editTextDescription:EditText
    private lateinit var editTextLocation:EditText
    private lateinit var editTextNumberSigned:EditText
    private lateinit var imageView:ImageView
    private val SCAN_BARCODE_REQUEST_CODE = 1001
    private lateinit var progressBar:ProgressBar

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        // Inflate the layout for this fragment
        val view = inflater.inflate(R.layout.fragment_add, container, false)

        val scanBtn: Button = view.findViewById(R.id.scan_btn)
        editTextBarcode = view.findViewById(R.id.editTextBarcode)
        editTextName = view.findViewById(R.id.editTextName)
        editTextDescription = view.findViewById(R.id.editTextDescription)
        editTextManufacture = view.findViewById(R.id.editTextManufacture)
        val spinnerWarehouse: Spinner = view.findViewById(R.id.spinnerWarehouse)
        editTextLocation = view.findViewById(R.id.editTextLocation)
        editTextNumberSigned = view.findViewById(R.id.editTextNumberSigned)
        imageView = view.findViewById(R.id.imageView)
        val addBtn: Button = view.findViewById(R.id.add_btn)
        progressBar = view.findViewById(R.id.progressBar) // Assuming you add a ProgressBar in XML

        // Initialize warehouse list
        warehousesList = mutableListOf()

        // Warehouse Adapter
        val adapterForWarehouses = ArrayAdapter(requireContext(), R.layout.spinner_item_layout, warehousesList)
        adapterForWarehouses.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerWarehouse.adapter = adapterForWarehouses

        // Retrieve server URL from SharedPreferences
        val preferences = requireActivity().getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        val savedServerURL = preferences.getString("server", "")

        if (savedServerURL.isNullOrBlank()) {
            Toast.makeText(requireContext(), "Server URL is missing. Please configure it in settings.", Toast.LENGTH_SHORT).show()
            return view
        }

        // Show progress while loading warehouses
        progressBar.visibility = View.VISIBLE

        // Create Retrofit instance
        val apiWarehouse = Retrofit.Builder()
            .baseUrl(savedServerURL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiServiceWarehouse::class.java)

        // API call to get Warehouses
        apiWarehouse.getWarehouses().enqueue(object : Callback<List<WarehouseDataClass>> {
            override fun onResponse(
                call: Call<List<WarehouseDataClass>>,
                response: retrofit2.Response<List<WarehouseDataClass>>
            ) {
                progressBar.visibility = View.GONE
                if (response.isSuccessful && response.body() != null) {
                    Log.d(TAG, "HTTP GET request is successful for warehouses")
                    response.body()?.let { warehouses ->
                        for (warehouse in warehouses) {
                            warehousesList.add(warehouse.name)
                        }
                        adapterForWarehouses.notifyDataSetChanged()
                    }
                } else {
                    Log.e(TAG, "Failed to load warehouses: ${response.errorBody()?.string()}")
                    Toast.makeText(requireContext(), "Failed to load warehouses.", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<List<WarehouseDataClass>>, t: Throwable) {
                progressBar.visibility = View.GONE
                Log.e(TAG, "HTTP GET request failed: ${t.message}")
                Toast.makeText(requireContext(), "Error loading warehouses.", Toast.LENGTH_SHORT).show()
            }
        })


        // Scan Action
        scanBtn.setOnClickListener(){

            //Clear all text values
            editTextName.setText("")
            editTextBarcode.setText("")
            editTextDescription.setText("")
            editTextManufacture.setText("")
            editTextLocation.setText("")
            editTextNumberSigned.setText("")


            val intent = Intent(requireContext(), BarcodeScanningActivity::class.java)
            startActivityForResult(intent, SCAN_BARCODE_REQUEST_CODE)

        } // end scan action


        return view
    }



    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == SCAN_BARCODE_REQUEST_CODE && resultCode == AppCompatActivity.RESULT_OK) {
            val barcodeValue = data?.getStringExtra("barcodeValue")
            if (barcodeValue != null) {
                editTextBarcode.setText(barcodeValue)


                //^^^^^^^^^^^^^^^^^^^^^^^^^^^
                try {
                    getProduct(barcodeValue)
                }catch (e: Exception){
                    Toast.makeText(requireContext(), "Product:${e.toString()}", Toast.LENGTH_SHORT).show()
                }

                //^^^^^^^^^^^^^^^^^^^^^^^^^^^
            }
        }
    }// onActivity result

    private fun getProduct(barcodeValue:String){
        //===============================================
        //send get request to server for the item with barcode = barcodeValue
        val preferences = requireActivity().getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        var token = preferences.getString("token", "")
        val savedServerURL = preferences.getString("server", "")

        //-------------send get request for product with barcode
        // Create Retrofit instance

        val apiGetProductByBarcode = Retrofit.Builder()
            .baseUrl(savedServerURL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiServiceProduct::class.java)

        //^^^^^call the api^^^^^^^^^^
        if(token == null)
        {
            token = "no login !"
        }
        apiGetProductByBarcode.getProduct(barcodeValue, token).enqueue(object : Callback<List<ProductDataClass>> {
            override fun onResponse(
                call: Call<List<ProductDataClass>>,
                response: retrofit2.Response<List<ProductDataClass>>
            ) {
                progressBar.visibility = View.GONE

                if (response.isSuccessful && response.body() != null) {
                    Log.d(TAG, "HTTP GET request is successful for Product")
                    response.body()?.let { products ->
                        for (product in products) {
                            //here only set the result to view
                            editTextName.setText(product.name)
                            editTextDescription.setText(product.description)
                            editTextManufacture.setText(product.manufacture)
                            editTextLocation.setText(product.location)
                            editTextNumberSigned.setText(product.stock)

                            Log.e(TAG, "setImageURI = ${savedServerURL +"static/img/uploads/"+ product.image}")

                            val imageUrl = savedServerURL +"static/img/uploads/"+ product.image
                            Glide.with(requireActivity()).load(imageUrl).into(imageView)


                        }

                    }
                } else {
                    Log.e(TAG, "Failed to load product: ${response.errorBody()?.string()}")
                    Toast.makeText(requireContext(), "Failed to load products.", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<List<ProductDataClass>>, t: Throwable) {
                progressBar.visibility = View.GONE
                Log.e(TAG, "HTTP GET request failed: ${t.message}")
                Toast.makeText(requireContext(), "Error loading warehouses.", Toast.LENGTH_SHORT).show()
            }
        })
        //===============================================
    }
}
