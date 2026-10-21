const {test}=require('node:test');
const assert=require('node:assert/strict');
const vm=require('node:vm');
const fs=require('node:fs');
const script=fs.readFileSync('public/assets/js/teacher-field-rules.js','utf8');
for(const [name,valid,invalid] of [
 ['nom', "N’Diaye", 'Ndiaye2'], ['prenom', 'Éléonore-Anne', 'Awa9'],
 ['telephone', '+221 77 123 45 67','77abc'], ['indice','0012','12e3'],
 ['cni','1234567890123','123ABC'], ['nombre_enfants','3','3.5'],
 ['compte_bancaire[code_guichet]','00001','A0001'], ['compte_bancaire[cle_rib]','01','AB']
]) test(`${name} enforces its character type on typing and pasted values`,()=>{
 const handlers={};vm.runInNewContext(script,{document:{addEventListener:(n,fn)=>handlers[n]=fn}});
 const field={name,value:'',selectionStart:0,selectionEnd:0,matches:()=>true,validity:{badInput:false},setCustomValidity(m){this.message=m},setAttribute(){},classList:{toggle(){}}};
 for(const [data,expected] of [[valid,false],[invalid,true]]){
  field.value='';const event={target:field,inputType:'insertText',data,preventDefault(){this.blocked=true}};
  handlers.beforeinput(event);assert.equal(!!event.blocked,expected);
  field.value=data;handlers.input({target:field});assert.equal(!!field.message,expected);
 }
});
