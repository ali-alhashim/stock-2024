package dev.alhashim.stock


import okhttp3.Cookie
import okhttp3.CookieJar
import okhttp3.HttpUrl

class SessionCookieJar : CookieJar {
    private val cookieStore: MutableMap<String, List<Cookie>> = mutableMapOf()

    override fun saveFromResponse(url: HttpUrl, cookies: List<Cookie>) {
        // Save cookies from the server response
        cookieStore[url.host()] = cookies
    }

    override fun loadForRequest(url: HttpUrl): List<Cookie> {
        // Return cookies for the request
        return cookieStore[url.host()] ?: emptyList()
    }
}