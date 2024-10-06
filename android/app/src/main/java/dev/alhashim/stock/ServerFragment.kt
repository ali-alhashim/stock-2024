package dev.alhashim.stock

import android.content.ContentValues.TAG
import android.content.Context
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.Toast
import com.google.android.material.textfield.TextInputEditText
import androidx.fragment.app.Fragment
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

class ServerFragment : Fragment() {

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        // Inflate the layout for this fragment
        val view = inflater.inflate(R.layout.fragment_server, container, false)

        val loginBtn: Button = view.findViewById(R.id.btnLogin)
        val username: TextInputEditText = view.findViewById(R.id.editTextUsername)
        val password: TextInputEditText = view.findViewById(R.id.editTextPassword)
        val serverURL: TextInputEditText = view.findViewById(R.id.editTextServerURL)

        // Check if SharedPreferences alhashim-stock already set the values to username, serverURL
        val preferences = requireActivity().getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        val savedUsername = preferences.getString("username", "")
        val savedServerURL = preferences.getString("server", "")

        // Set the previously saved username and server URL if they exist
        username.setText(savedUsername)
        serverURL.setText(savedServerURL)

        loginBtn.setOnClickListener {
            val usernameText = username.text.toString().trim()
            val passwordText = password.text.toString().trim()
            val serverURLText = serverURL.text.toString().trim()

            if (usernameText.isEmpty() || passwordText.isEmpty() || serverURLText.isEmpty()) {
                Toast.makeText(requireContext(), "Please fill all fields", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }


            // check the server url start with http or https and then check if the server is reachable
            if (!serverURLText.startsWith("http://") && !serverURLText.startsWith("https://")) {
                Toast.makeText(requireContext(), "Server URL must start with http:// or https://", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }


            Log.d(TAG, "Login clicked: username => $usernameText, password => $passwordText, server => $serverURLText")

            //----------------- send post request --------------
            val retrofit = Retrofit.Builder()
                .baseUrl(serverURLText)  // Get the correct URL string from TextInputEditText
                .addConverterFactory(GsonConverterFactory.create())
                .build()

            val apiService = retrofit.create(ApiServiceLogin::class.java)
            val call = apiService.login(usernameText, passwordText, "login", "android")

            call.enqueue(object : Callback<LoginDataClass> {
                override fun onResponse(call: Call<LoginDataClass>, response: Response<LoginDataClass>) {
                    if (response.isSuccessful) {
                        Log.d(TAG, "Response: ${response.body().toString()}")

                        // Handle successful response
                        response.body()?.let { loginResponse ->
                            if (loginResponse.status == "success") {
                                // Save token, server URL, and username
                                preferences.edit().apply {
                                    putString("token", loginResponse.token)
                                    putString("server", serverURLText)
                                    putString("username", loginResponse.username)
                                    apply()
                                }

                                Toast.makeText(requireContext(), "Welcome: ${loginResponse.username}", Toast.LENGTH_LONG).show()

                                // Navigate to AddFragment
                                parentFragmentManager.beginTransaction()
                                    .replace(R.id.fragmentContainerView, AddFragment())
                                    .commit()
                            } else {
                                Toast.makeText(requireContext(), loginResponse.message, Toast.LENGTH_LONG).show()
                            }
                        }
                    } else {
                        Log.e(TAG, "Login failed. HTTP error code: ${response.code()}")
                        Toast.makeText(requireContext(), "Login failed. Error code: ${response.code()}", Toast.LENGTH_LONG).show()
                    }
                }

                override fun onFailure(call: Call<LoginDataClass>, t: Throwable) {
                    Log.e(TAG, "Connection failed: ${t.message}")
                    Toast.makeText(requireContext(), "Failed to connect to server. Check URL and try again.", Toast.LENGTH_LONG).show()
                }
            })
            //---------------------------------------------------
        }

        return view
    }
}
