package dev.alhashim.stock


object CookieManager {
        val cookieJar: SessionCookieJar by lazy {
            SessionCookieJar() // Creates a single instance of your CookieJar
        }
}