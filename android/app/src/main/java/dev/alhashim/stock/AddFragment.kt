package dev.alhashim.stock

import android.app.Activity
import android.content.ContentValues.TAG
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.net.Uri
import android.Manifest
import android.app.ProgressDialog
import android.graphics.drawable.BitmapDrawable
import android.os.Bundle
import android.provider.MediaStore
import android.text.Editable
import android.text.TextWatcher
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
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.widget.doOnTextChanged
import androidx.fragment.app.Fragment
import com.bumptech.glide.Glide
import com.google.gson.GsonBuilder
import okhttp3.MediaType
import okhttp3.MultipartBody
import okhttp3.OkHttpClient
import okhttp3.RequestBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.io.File
import java.io.FileOutputStream
import java.io.IOException

class AddFragment : Fragment() {

    private lateinit var warehousesList: MutableList<String>
    private lateinit var editTextBarcode: EditText
    private  lateinit var editTextName:EditText
    private lateinit var editTextManufacture:EditText
    private lateinit var editTextDescription:EditText
    private lateinit var editTextLocation:EditText
    private lateinit var editTextNumberSigned:EditText
    private lateinit var editTextQuantity:EditText
    private lateinit var editTextNewStock:EditText
    private lateinit var imageView:ImageView
    private val SCAN_BARCODE_REQUEST_CODE = 1001
    private lateinit var progressBar:ProgressBar
    private lateinit var preferences:SharedPreferences
    private lateinit var token:String
    private lateinit var username:String
    private lateinit var savedServerURL:String





    private val cameraIntentLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val imageBitmap = result.data?.extras?.get("data") as Bitmap
            imageView.setImageBitmap(imageBitmap) // Set image to ImageView
        }
    }


    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        // Inflate the layout for this fragment
        val view = inflater.inflate(R.layout.fragment_add, container, false)

        preferences    = requireActivity().getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        token          = preferences.getString("token", "").toString()
        username       = preferences.getString("username", "").toString()
        savedServerURL = preferences.getString("server", "").toString()




        val scanBtn: Button = view.findViewById(R.id.scan_btn)
        editTextBarcode = view.findViewById(R.id.editTextBarcode)
        editTextName = view.findViewById(R.id.editTextName)
        editTextDescription = view.findViewById(R.id.editTextDescription)
        editTextManufacture = view.findViewById(R.id.editTextManufacture)
        val spinnerWarehouse: Spinner = view.findViewById(R.id.spinnerWarehouse)
        editTextLocation = view.findViewById(R.id.editTextLocation)
        editTextNumberSigned = view.findViewById(R.id.editTextNumberSigned) // current stock quantity
        editTextQuantity     = view.findViewById(R.id.editTextQuantity) // in / out quantity
        editTextNewStock     = view.findViewById(R.id.editTextNewStock) // the new Stock quantity = current stock quantity + (in / out quantity)
        imageView = view.findViewById(R.id.imageView)
        val addBtn: Button = view.findViewById(R.id.add_btn)
        progressBar = view.findViewById(R.id.progressBar) // Assuming you add a ProgressBar in XML

        // Initialize warehouse list
        warehousesList = mutableListOf()

        // Warehouse Adapter
        val adapterForWarehouses = ArrayAdapter(requireContext(), R.layout.spinner_item_layout, warehousesList)
        adapterForWarehouses.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerWarehouse.adapter = adapterForWarehouses




        if (savedServerURL.isBlank()) {
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
        apiWarehouse.getWarehouses(token).enqueue(object : Callback<List<WarehouseDataClass>> {
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

        // -----++++++ Add Product

        addBtn.setOnClickListener {
            // Validate inputs before proceeding
            if (editTextName.text.isNullOrBlank() || editTextNewStock.text.isNullOrBlank() || editTextBarcode.text.isNullOrBlank()) {
                Toast.makeText(requireContext(), "Please fill in all required fields", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }

            if (imageView.drawable == null) {
                Toast.makeText(requireContext(), "Please select an image", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }

            // Prepare data for API call
            val theUsername = RequestBody.create(MediaType.parse("text/plain"), username)
            val function = RequestBody.create(MediaType.parse("text/plain"), "addProduct")
            val device = RequestBody.create(MediaType.parse("text/plain"), "android")
            val token = RequestBody.create(MediaType.parse("text/plain"), token)
            val name = RequestBody.create(MediaType.parse("text/plain"), editTextName.text.toString())
            val newStock = RequestBody.create(MediaType.parse("text/plain"), editTextNewStock.text.toString())
            val description = RequestBody.create(MediaType.parse("text/plain"), editTextDescription.text.toString())
            val manufacture = RequestBody.create(MediaType.parse("text/plain"), editTextManufacture.text.toString())
            val location = RequestBody.create(MediaType.parse("text/plain"), editTextLocation.text.toString())
            val warehouse_id = RequestBody.create(MediaType.parse("text/plain"), spinnerWarehouse.selectedItem.toString())
            val barcode = RequestBody.create(MediaType.parse("text/plain"), editTextBarcode.text.toString())

            // Get the image from ImageView, convert it to a file
            val bitmap = (imageView.drawable as BitmapDrawable).bitmap
            val file = File(requireContext().cacheDir, editTextBarcode.text.toString() + ".jpg")

            // Write bitmap to file
            val outStream = FileOutputStream(file)
            bitmap.compress(Bitmap.CompressFormat.JPEG, 100, outStream)
            outStream.flush()
            outStream.close()

            // Create a RequestBody for the image
            val imageRequestBody = RequestBody.create(MediaType.parse("image/jpeg"), file)
            val theImage = MultipartBody.Part.createFormData("image", file.name, imageRequestBody)

            // Create the Retrofit instance and API service
            val gson = GsonBuilder()
                .setLenient()
                .create()





            // Create OkHttpClient with the cookie jar
            val okHttpClient = OkHttpClient.Builder()
                .cookieJar(CookieManager.cookieJar) // Use your custom CookieJar
                .build()




            val retrofit = Retrofit.Builder()
                .baseUrl(savedServerURL)  // Get the correct URL string from TextInputEditText
                .client(okHttpClient)
                .addConverterFactory(GsonConverterFactory.create(gson))
                .build()

            val apiService = retrofit.create(ApiServiceAddProduct::class.java)

            // Call the API with the data
            val call = apiService.addProduct(theUsername, function, device, token, name, theImage, newStock, description, manufacture, location, warehouse_id, barcode)

            // Show a loading spinner or progress indicator
            val loadingDialog = ProgressDialog(requireContext())
            loadingDialog.setMessage("Saving product, please wait...")
            loadingDialog.setCancelable(false)
            loadingDialog.show()

            // Enqueue the API call
            call.enqueue(object : Callback<AddProductDataClass> {
                override fun onResponse(call: Call<AddProductDataClass>, response: Response<AddProductDataClass>) {
                    loadingDialog.dismiss() // Dismiss loading dialog
                    Log.d(TAG, "Raw response: ${response.raw().toString()}")

                    if (response.isSuccessful) {
                        // Handle successful response
                        response.body()?.let { response ->
                            if (response.status == "success") {
                                Toast.makeText(requireContext(), "The Product Saved Successfully", Toast.LENGTH_LONG).show()
                                // Optionally clear the form after a successful save
                            } else {
                                Toast.makeText(requireContext(), response.message, Toast.LENGTH_LONG).show()
                            }
                        }

                        //call scanbarcode agin to update
                        try {
                            getProduct(barcode.toString())
                        }catch (e: Exception){
                            Toast.makeText(requireContext(), "Product:${e.toString()}", Toast.LENGTH_SHORT).show()
                        }

                    }
                    else {
                        Log.e(TAG, "Saving product failed. HTTP error code: ${response.body()?.message}")
                        Toast.makeText(requireContext(), "Saving product failed. Error code: ${response.code()}", Toast.LENGTH_LONG).show()
                    }
                }

                override fun onFailure(call: Call<AddProductDataClass>, t: Throwable) {
                    loadingDialog.dismiss() // Dismiss loading dialog
                    Log.e(TAG, "Connection failed: ${t.message}")
                    //call scanbarcode agin to update
                    try {
                        getProduct(barcode.toString())
                    }catch (e: Exception){
                        Toast.makeText(requireContext(), "Product:${e.toString()}", Toast.LENGTH_SHORT).show()
                    }

                    Toast.makeText(requireContext(), "Failed to connect to server. Check URL and try again. ${t.message}", Toast.LENGTH_LONG).show()

                }
            })

            //------------------------------------------------------------
        }






        //TextWatcher ---------
        // TextWatcher for detecting changes in the EditText fields
        val textWatcher = object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {
                // No action needed here
            }

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                calculateNewStock() // Calculate and update new stock when text changes
            }

            override fun afterTextChanged(s: Editable?) {
                // No action needed here
            }
        }
        //End TextWatcher------


        // Attach TextWatcher to both EditText fields
        editTextNumberSigned.addTextChangedListener(textWatcher)
        editTextQuantity.addTextChangedListener(textWatcher)



        // ImageView Click open Camera take photo set to self
         val cameraIntentLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
            if (result.resultCode == Activity.RESULT_OK) {
                val imageBitmap = result.data?.extras?.get("data") as Bitmap
                imageView.setImageBitmap(imageBitmap) // Set image to ImageView
            }
        }



        //-------------------------------------------------





        return view
    } // end OnCreateView


    // Handle permissions request
    val permissionRequestLauncher = registerForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
        if (granted) {
            openCamera()  // Permission granted, open camera
        } else {
            // Permission denied, handle accordingly (e.g., show message)
        }
    }


    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        imageView = view.findViewById(R.id.imageView) // Find your ImageView

        // Set OnClickListener on ImageView to open the camera
        imageView.setOnClickListener {
            // Check for camera permission
            if (ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
                permissionRequestLauncher.launch(Manifest.permission.CAMERA)
            } else {
                openCamera() // If permission already granted, open the camera
            }
        }
    }

    private fun openCamera() {
        val cameraIntent = Intent(MediaStore.ACTION_IMAGE_CAPTURE)
        cameraIntentLauncher.launch(cameraIntent)
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


        //-------------send get request for product with barcode
        // Create Retrofit instance

        val apiGetProductByBarcode = Retrofit.Builder()
            .baseUrl(savedServerURL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiServiceProduct::class.java)

        //^^^^^call the api^^^^^^^^^^

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
    } //end get Product

    // Function to calculate and update the new stock quantity
    fun calculateNewStock() {
        val currentStock = editTextNumberSigned.text.toString().toIntOrNull() ?: 0 // Get current stock or 0 if input is invalid
        val inOutQuantity = editTextQuantity.text.toString().toIntOrNull() ?: 0 // Get in/out quantity or 0 if input is invalid

        val newStockQuantity = currentStock + inOutQuantity // Calculate new stock quantity
        editTextNewStock.setText(newStockQuantity.toString()) // Update the new stock EditText with the result
    }



}//end class
