from flask import Flask, request, render_template
from classes import Source, Result
import os
import gemini_incl as gem

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'

def create_last_dict(files,tab):
    last_dict={}
    for idx, file in enumerate(files, start=1):
        last_dict.update({file.filename: tab[idx-1]})

@app.route('/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        selected_option = request.form.get('option')
        mod = request.form.get('mod')
        files = request.files.getlist('files')

        #print(selected_option)
        #print(files)

        source = Source(files, selected_option, mod, upload_folder=app.config['UPLOAD_FOLDER'])
        mid_res = Result(source)
        mid_res_dict = mid_res.to_dict()  # ✅ {"f1.pdf": "texte modifié...", ...}
        ai = gem.AI()
        source_tab =[]
        out_ai=[]
        
        for i in mid_res_dict.values():
            source_tab.append(i)
        for i in range(1,len(source_tab)):
            source_tab[i]="---SOURCE_START---"+source_tab[i]+"---SOURCE_END---"
        
        match selected_option:
            case "2":
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
            case "3":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
            case "4":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                out_ai.append(ai.invoke_quiz(out_ai[0],gem.translate(mod)))
            case _:
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))

        #last_dict = create_last_dict(files,out_ai)
        print(len(out_ai))
        temp=0
        for i in out_ai:
            print(temp)
            print(f"\n{i}")
            temp+=1

        return render_template('result.html', results=out_ai)#result = final array for abstract

    return render_template('index.html')
if __name__ == "__main__":
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)