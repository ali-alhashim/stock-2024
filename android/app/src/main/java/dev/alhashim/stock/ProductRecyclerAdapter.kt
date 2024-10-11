package dev.alhashim.stock

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

class ProductRecyclerAdapter(private val productList:List<ProductDataClass>):RecyclerView.Adapter<ProductRecyclerAdapter.ProductViewHolder>() {

    class ProductViewHolder(itemView: View):RecyclerView.ViewHolder(itemView){
        val productImage: ImageView = itemView.findViewById(R.id.productImage)
        val productName : TextView  = itemView.findViewById(R.id.productNameTextView)

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
        holder.productName.text = currentProduct.name
    }
}