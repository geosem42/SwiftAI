import{d as v,r as i,o as n,b as y,w as t,k as r,e as a,i as l,F as k,l as b,j,f as e,n as m,y as S,m as B,v as M,t as d}from"./app-9e33c640.js";import{J as C}from"./ActionMessage-5c5087b8.js";import{J,a as L}from"./DialogModal-875d4055.js";import{J as V}from"./Button-6a91530d.js";import{J as O}from"./Input-2edcdd1e.js";import{J as $}from"./InputError-fc966430.js";import{J as x}from"./SecondaryButton-332648cf.js";import{_ as D}from"./_plugin-vue_export-helper-c27b6911.js";const F=v({props:["sessions"],components:{JetActionMessage:C,JetActionSection:J,JetButton:V,JetDialogModal:L,JetInput:O,JetInputError:$,JetSecondaryButton:x},data(){return{form:this.$inertia.form({password:""}),modal:null}},methods:{confirmLogout(){this.form.password="";let o=document.querySelector("#confirmingLogoutModal");this.modal=new bootstrap.Modal(o),this.modal.show(),setTimeout(()=>this.$refs.password.focus(),250)},logoutOtherBrowserSessions(){this.form.delete(route("other-browser-sessions.destroy"),{preserveScroll:!0,onSuccess:()=>this.closeModal(),onError:()=>this.$refs.password.focus(),onFinish:()=>this.form.reset()})},closeModal(){this.modal.hide(),this.form.reset()}}}),I=e("div",null," If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password. ",-1),N={key:0,class:"mt-3"},z={class:"d-flex"},E={key:0,fill:"none",width:"32","stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",viewBox:"0 0 24 24",stroke:"currentColor",class:"text-muted"},K=e("path",{d:"M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"},null,-1),T=[K],U={key:1,xmlns:"http://www.w3.org/2000/svg",width:"32",viewBox:"0 0 24 24","stroke-width":"2",stroke:"currentColor",fill:"none","stroke-linecap":"round","stroke-linejoin":"round",class:"text-muted"},A=e("path",{d:"M0 0h24v24H0z",stroke:"none"},null,-1),H=e("rect",{x:"7",y:"4",width:"10",height:"16",rx:"1"},null,-1),P=e("path",{d:"M11 5h2M12 17v.01"},null,-1),q=[A,H,P],G={class:"ms-2"},Q={class:"small font-weight-lighter text-muted"},R={key:0,class:"text-success font-weight-bold"},W={key:1},X={class:"d-flex mt-3"},Y={class:"form-group mt-3 w-md-75"},Z={class:"spinner-border spinner-border-sm",role:"status"},oo=e("span",{class:"visually-hidden"},"Loading...",-1),eo=[oo];function so(o,c,to,ro,no,io){const h=i("jet-action-message"),u=i("jet-button"),p=i("jet-input"),_=i("jet-input-error"),f=i("jet-secondary-button"),w=i("jet-dialog-modal"),g=i("jet-action-section");return n(),y(g,null,{title:t(()=>[r(" Browser Sessions ")]),description:t(()=>[r(" Manage and log out your active sessions on other browsers and devices. ")]),content:t(()=>[a(h,{on:o.form.recentlySuccessful},{default:t(()=>[r(" Done. ")]),_:1},8,["on"]),I,o.sessions.length>0?(n(),l("div",N,[(n(!0),l(k,null,b(o.sessions,s=>(n(),l("div",z,[e("div",null,[s.agent.is_desktop?(n(),l("svg",E,T)):(n(),l("svg",U,q))]),e("div",G,[e("div",null,d(s.agent.platform?s.agent.platform:"Unknown")+" - "+d(s.agent.browser?s.agent.browser:"Unknown"),1),e("div",null,[e("div",Q,[r(d(s.ip_address)+", ",1),s.is_current_device?(n(),l("span",R,"This device")):(n(),l("span",W,"Last active "+d(s.last_active),1))])])])]))),256))])):j("",!0),e("div",X,[a(u,{onClick:o.confirmLogout},{default:t(()=>[r(" Log out Other Browser Sessions ")]),_:1},8,["onClick"])]),a(w,{id:"confirmingLogoutModal"},{title:t(()=>[r(" Log out Other Browser Sessions ")]),content:t(()=>[r(" Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices. "),e("div",Y,[a(p,{type:"password",placeholder:"Password",ref:"password",class:m({"is-invalid":o.form.errors.password}),modelValue:o.form.password,"onUpdate:modelValue":c[0]||(c[0]=s=>o.form.password=s),onKeyup:S(o.logoutOtherBrowserSessions,["enter"])},null,8,["class","modelValue","onKeyup"]),a(_,{message:o.form.errors.password},null,8,["message"])])]),footer:t(()=>[a(f,{"data-dismiss":"modal",onClick:o.closeModal},{default:t(()=>[r(" Cancel ")]),_:1},8,["onClick"]),a(u,{class:m(["ms-2",{"text-white-50":o.form.processing}]),onClick:o.logoutOtherBrowserSessions,disabled:o.form.processing},{default:t(()=>[B(e("div",Z,eo,512),[[M,o.form.processing]]),r(" Log out Other Browser Sessions ")]),_:1},8,["onClick","class","disabled"])]),_:1})]),_:1})}const fo=D(F,[["render",so]]);export{fo as default};
