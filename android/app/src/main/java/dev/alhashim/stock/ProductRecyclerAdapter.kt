package dev.alhashim.stock

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide

class ProductRecyclerAdapter(private val context: Context,private val productList:List<ProductDataClass>):RecyclerView.Adapter<ProductRecyclerAdapter.ProductViewHolder>() {

    class ProductViewHolder(itemView: View):RecyclerView.ViewHolder(itemView){
        val productImage: ImageView = itemView.findViewById(R.id.productImage)
        val productName : TextView  = itemView.findViewById(R.id.productNameTextView)
        val description : TextView  = itemView.findViewById(R.id.descriptionTextView)
        val location    : TextView  = itemView.findViewById(R.id.productLocationTextView)
        val quantity    : TextView  = itemView.findViewById(R.id.productQuantityTextView)
        val warehouse   : TextView  = itemView.findViewById(R.id.warehouseTextView)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ProductViewHolder {
                                                                           //xml file name for ProductViewHolder
        val itemView = LayoutInflater.from(parent.context).inflate(R.layout.product_box, parent, false)
        return ProductViewHolder(itemView)
    }

    override fun getItemCount(): Int {
        return productList.size
    }

    override fun onBindViewHolder(holder: ProductViewHolder, position: Int) {
        val currentProduct = productList[position]
        val preferences    = context.getSharedPreferences("alhashim-stock", Context.MODE_PRIVATE)
        val savedServerURL = preferences.getString("server", "").toString()
        holder.productName.text = currentProduct.name
        val imageUrl = savedServerURL+"static/img/uploads/"+currentProduct.image
        Glide.with(holder.itemView).load(imageUrl).into(holder.productImage)
        holder.description.text = currentProduct.description
        holder.location.text    = currentProduct.location
        holder.quantity.text    = currentProduct.stock
        holder.warehouse.text   = currentProduct.warehouse
    }
}