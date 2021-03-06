<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use Cart;
use App\Http\Requests;
use Session;

class DistributorCartController extends Controller
{
    public function distributor_addcart(Request $request,$Fid)
    {
        $quantity=$request->quantity;
        $Cus_id=$request->session()->get('Uid');

       // $product_id=$request->product_id;
        $product_info=DB::table('food')
                        ->where('Fid',$Fid)
                        ->first();
        
        $data=array();
        $cus_req_qty=$data['qty']=$quantity;
        $data['Fid']=$product_info->Fid;
        $data['Fname']=$product_info->Fname;
        $data['price']=$product_info->SRate;
        $data['Fimage']=$product_info->Fimage;
        $data['Uid']=$Cus_id;
        
        $product=DB::table('food')
                        ->where('Fid',$Fid)
                        ->first();
        $product_total_qty=$product->Quantity;                
        if($cus_req_qty<=$product_total_qty)
        {
        $existing=DB::table('dcart')
         ->where('Fid',$Fid)
         ->first();
        if($existing)
        {
            $new_qty=$request->quantity;
            $existing_qty=$existing->qty;
            $product_total_qty=$existing_qty+$new_qty;
            if($cus_req_qty<=$product_total_qty)
        {   
            $data['qty']=$product_total_qty;
            DB::table('dcart')
            ->where('Fid',$Fid)
            ->update($data);
        
        //food table qty (-)
        $data2=array();
        $total_food=DB::table('food')
         ->where('Fid',$Fid)
         ->first();
         $data2['Quantity']=$total_food->Quantity-$new_qty; 

        DB::table('food')
        ->where('Fid',$Fid)
        ->update($data2);
    

            return redirect('/distributor_show_cart');
        
        }
        else{
            echo "not enough";
        }   
        }

            else{

        

        DB::table('dcart')->insert($data);
        
        
                //food table qty (-)
                $data2=array();
                $total_food=DB::table('food')
                 ->where('Fid',$Fid)
                 ->first();
                 $data2['Quantity']=$total_food->Quantity-$cus_req_qty; 
        
                DB::table('food')
                ->where('Fid',$Fid)
                ->update($data2);
            
        
        return redirect('/distributor_show_cart');
            }
    }
        else{
                echo "not enough food";
        }
        
            
    }

    public function distributor_show_cart(Request $request)
    {
        
        $Cus_id=$request->session()->get('Uid');

        $all_cart_product=DB::table('dcart')
                        ->where('Uid',$Cus_id)
                        ->get();
        
        
        return view('distributor/distributor_show_cart',compact('all_cart_product'));
            
    }

    public function update_cart(Request $request,$Fid)
    {   
        
        $data=array();
        $data['qty']=$request->qty;
        //$quantity=$request->quantity;
        DB::table('dcart')
         ->where('Fid',$Fid)
         ->update($data);
        
        return redirect('/distributor_show_cart');
            
    }

    public function delete_cart($Fid)
    {
        DB::table('dcart')
            ->where('Fid',$Fid)
            ->delete();
            return redirect('/distributor_show_cart');
    }

    public function distributor_checkout(Request $request)
    {
        
        $Cus_id=$request->session()->get('Uid');

        $cus_info=DB::table('user')
                        ->where('Uid',$Cus_id)
                        ->first();

        $all_cart_product=DB::table('dcart')
        ->where('Uid',$Cus_id)
        ->get();                
        
        
        return view('distributor/distributor_checkout',compact('cus_info','all_cart_product'));
            
    }

    public function confirm_checkout(Request $request)
    {
        
        $Cus_id=$request->session()->get('Uid');
            
        
       $all_cart_product=DB::table('dcart')
        ->where('Uid',$Cus_id)
        ->get();
        
        foreach($all_cart_product as $cart)
        {
            $total=$cart->qty*$cart->price;

            $Cus_id=$request->session()->get('Uid');
            $data=array();            
            $data['distributor_id']=$Cus_id;          
            $data['name']=$request->name;
           $data['email']=$request->email;
           $data['phone']=$request->phone;
           $data['address']=$request->address;
            $data['Fid']=$cart->Fid;
            $data['Fname']=$cart->Fname;
            $data['Quantity']=$cart->qty;
            $data['Total_price']=$total;
            $data['Status']='Pending';
        
            DB::table('dorders')->insert($data);
        }
        DB::table('cart')
        ->where('Cus_id',$Cus_id)
        ->delete();
            
        return redirect('/distributor_show_cart');
    }


}
