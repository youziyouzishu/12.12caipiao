import{z as e,A as s,p as a,L as t,ap as l,aE as o,o as r,u as c,x as i,aG as n,c as u,w as f,i as m,r as p,e as d,a as h,b as g,g as x,m as b,d as v,l as _,aH as y,I as w,aI as k,Y as F,Q as S}from"./index-Dmu3mr1_.js";import{_ as j}from"./uv-navbar.CCP2bw2Z.js";import{_ as C,a as q,b as N,c as $,d as I,e as J,f as O}from"./share_download.BdbCiIUT.js";import{_ as T}from"./_plugin-vue_export-helper.BCo6x5W8.js";import"./uv-status-bar.C2pO0b0Y.js";import"./mixin.qHq9NBSw.js";import"./uv-icon.zJBDCHES.js";const P=T({__name:"share-poster",setup(T){const P=e(),{userinfo:E}=s(P),Q=a(),U=t({cover:"",title:"",qrCode:""}),W=l().appName,X=y+"?invitecode=",Y=o((()=>X+E.value.invitecode+`&time=${(new Date).getTime()}`)),z=e=>{switch(e){case"wx":A();break;case"wxpyq":B();break;case"haibao":H();break;case"link":console.log("复制链接"),w({data:Y.value})}},A=()=>{uni.share({provider:"weixin",scene:"WXSceneSession",type:0,href:Y.value,title:W,imageUrl:D.value,summary:`我正在使用${W},快来下载吧：${Y.value}`,success:function(e){console.log("success:"+JSON.stringify(e))},fail:function(e){console.log("fail:"+JSON.stringify(e))}})},B=()=>{uni.share({provider:"weixin",scene:"WXSceneTimeline",type:0,href:Y.value,title:W,summary:`我正在使用${W},快来下载吧：${Y.value}`,imageUrl:D.value,success:function(e){console.log("success:"+JSON.stringify(e))},fail:function(e){console.log("fail:"+JSON.stringify(e))}})},D=a(""),G=e=>{console.log("haibaoSuccess path",e),D.value=e},H=()=>{k({filePath:D.value,success:e=>{F(),S("保存图片成功!").then((()=>{}))}})};return r((e=>{console.log("options",e);const s=c.getItem("config");console.log("config",s),s&&(U.cover=i(s.poster_image),U.title=s.invite_rule),n().then((e=>{console.log("getPoster",e),U.qrCode=e.base64}))})),(e,s)=>{const a=p(d("uv-navbar"),j),t=p(d("l-painter-image"),C),l=p(d("l-painter-text"),q),o=p(d("l-painter-view"),N),r=p(d("l-painter"),$),c=m,i=v,n=_;return h(),u(c,{class:"p-32"},{default:f((()=>[g(a,{title:"推广海报",fixed:"","auto-back":"","bg-color":"#E42B28","title-style":"color: #FFFFFF;","left-icon-color":"#FFFFFF",placeholder:""}),g(c,{class:"poster-box"},{default:f((()=>[g(r,{ref_key:"painter",ref:Q,isCanvasToTempFilePath:"",onSuccess:G},{default:f((()=>[U.cover?(h(),u(t,{key:0,src:U.cover,css:"width: 686rpx; height: 588rpx; object-fit: cover;"},null,8,["src"])):x("",!0),g(o,{css:"margin-top:20rpx; display: flex; padding-bottom: 20rpx;"},{default:f((()=>[g(l,{css:"flex: 1;",text:U.title},null,8,["text"]),g(t,{src:U.qrCode,css:"width: 126rpx; height: 126rpx; margin-left: 46rpx;"},null,8,["src"])])),_:1})])),_:1},512)])),_:1}),g(c,{class:"mt-20 share-box"},{default:f((()=>[g(c,{class:"share-title"},{default:f((()=>[b(" 发给好友 ")])),_:1}),g(c,{class:"share-sub-title mt-8"},{default:f((()=>[b(" 分享给你的好朋友吧 ")])),_:1}),g(c,{class:"mt-40 flex items-center justify-between px-28"},{default:f((()=>[g(c,{class:"share-btn center flex-column",onClick:s[0]||(s[0]=e=>z("wx"))},{default:f((()=>[g(i,{class:"share-btn-image",src:I,mode:""}),g(n,{class:"share-btn-text mt-8"},{default:f((()=>[b("微信好友")])),_:1})])),_:1}),g(c,{class:"share-btn center flex-column",onClick:s[1]||(s[1]=e=>z("wxpyq"))},{default:f((()=>[g(i,{class:"share-btn-image",src:J,mode:""}),g(n,{class:"share-btn-text mt-8"},{default:f((()=>[b("朋友圈")])),_:1})])),_:1}),g(c,{class:"share-btn center flex-column",onClick:s[2]||(s[2]=e=>z("haibao"))},{default:f((()=>[g(i,{class:"share-btn-image",src:O,mode:""}),g(n,{class:"share-btn-text mt-8"},{default:f((()=>[b("下载图片")])),_:1})])),_:1}),g(c,{class:"share-btn center flex-column",onClick:s[3]||(s[3]=e=>z("link"))},{default:f((()=>[g(i,{class:"share-btn-image",src:"/h5/static/images/share_link.png",mode:""}),g(n,{class:"share-btn-text mt-8"},{default:f((()=>[b("复制链接")])),_:1})])),_:1})])),_:1})])),_:1})])),_:1})}}},[["__scopeId","data-v-8d2ecf86"]]);export{P as default};
