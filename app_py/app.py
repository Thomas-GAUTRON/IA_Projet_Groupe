from flask import Flask, request, render_template
from flask_cors import CORS
from classes import Source, Result
import os
import gemini_incl as gem
import result_prep as prep
'''
Main File for the complete application
'''
app = Flask(__name__)
CORS(app)
current_directory = os.path.dirname(os.path.abspath(__file__))
app.config['UPLOAD_FOLDER'] = f'{current_directory}/uploads'

# Add route to serve PDF files from download directory
@app.route('/download/<path:filename>')
def download_file(filename):
    from flask import send_from_directory
    return send_from_directory(f'{current_directory}/download', filename)

# Add route to serve CSS file from templates directory
@app.route('/style.css')
def serve_css():
    from flask import send_from_directory
    return send_from_directory('templates', 'style.css')

# Main route for the application
@app.route('/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        selected_option = request.form.get('option')
        mod = request.form.get('modifier')
        files = request.files.getlist('files')
        edu = request.files.get('mode')
        if not files or (len(files) == 1 and files[0].filename == ''):
            files = request.files.getlist('files[]')

        #All variables used in the following code
        source = Source(files, selected_option, mod, upload_folder=app.config['UPLOAD_FOLDER'])
        mid_res = Result(source)
        mid_res_dict = mid_res.to_dict()
        ai = gem.AI()
        source_tab =[]
        out_ai=[]
        cleaned_ai=[]
        quiz_file_name = []
        results_with_pdfs = []
        
        for i in mid_res_dict.values():
            source_tab.append(i)
        
        # Creation of the source_tab used by the AI
        for i in range(1,len(source_tab)):
            source_tab[i]="---SOURCE_START---"+source_tab[i]+"---SOURCE_END---"
        
        match selected_option:

            case "2":# Case for only quiz
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                #print(f"out_ai : {out_ai}")

                cleaned_ai,temp = prep.clean_json(out_ai[0])
                #print(f"cleaned_ai : {cleaned_ai}")
                #print(f"len : {len(cleaned_ai)}")

                for i in range(0,len(cleaned_ai)):
                    #print(f"type : {type(cleaned_ai[i])}")
                    prep.to_json(cleaned_ai[i],f"quiz_{files[i].filename.replace('pdf','json')}")
                    quiz_file_name.append(os.path.abspath(f"quiz_{files[i].filename.replace('pdf','json')}"))


            case "3": # Case for Abstract and quiz from the whole source
                # Creation of the abstract
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_split_str(out_ai[0])
                #print(f"Cleaned_abstract : {temp}")
                #print(f"len_cleaned_abstract : {len_cleaned_ai}")

                #Add each string abstrat cleaned to the cleaned_ai array
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])

                # Creation of the quiz
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_json(out_ai[1])
                #print(f"Cleaned_quiz : {temp}")
                #print(f"len_cleaned_quiz : {len_cleaned_ai}")

                #Add each string quiz cleaned to the cleaned_ai array
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
                """
                # Parsing of cleaned results to create the PDFs 
                for i in range(0,len(cleaned_ai)-len_cleaned_ai):
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")

                    relative_pdf_path = os.path.join(f'{current_directory}/download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })

                # Parsing of the reste of cleaned results to create the JSONs
                for i in range(0,len_cleaned_ai):
                    prep.to_json(cleaned_ai[i+len_cleaned_ai],f"quiz_{files[i].filename.replace('pdf','json')}")
                    quiz_file_name.append(os.path.abspath(f"quiz_{files[i].filename.replace('pdf','json')}"))
                """  

            case "4":# Case for abstract and quiz from the abstract
                # Creation of the abstract
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_split_str(out_ai[0])
                #print(f"Cleaned_abstract : {temp}")
                #print(f"len_cleaned_abstract : {len_cleaned_ai}")

                #Add each string abstrat cleaned to the cleaned_ai array
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])

                # Creation of the quiz
                out_ai.append(ai.invoke_quiz(cleaned_ai,gem.translate(mod)))
                temp, len_cleaned_ai = prep.clean_json(out_ai[1])
                #print(f"Cleaned_quiz : {temp}")
                #print(f"len_cleaned_quiz : {len_cleaned_ai}")

                #Add each string quiz cleaned to the cleaned_ai array
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])

                # Parsing of cleaned results to create the PDFs 
                for i in range(0,len(cleaned_ai)-len_cleaned_ai):
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")

                    relative_pdf_path = os.path.join(f'{current_directory}/download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })

                # Parsing of the reste of cleaned results to create the JSONs
                for i in range(0,len_cleaned_ai):
                    prep.to_json(cleaned_ai[i+len_cleaned_ai],f"quiz_{files[i].filename.replace('pdf','json')}")
                    quiz_file_name.append(os.path.abspath(f"quiz_{files[i].filename.replace('pdf','json')}"))
                
            case _:# Base case for the creation of abstract only
                #Creation and cleaningof the abstract
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod),gem.translate(edu)))
                #print(f"out_ai : {out_ai}")
                cleaned_ai, temp = prep.clean_split_str(out_ai[0])
                #print(f"Cleaned_abstract : {cleaned_ai}")
                #print(f"len_cleaned_abstract : {len(cleaned_ai)}")

                # Parsing of cleaned results to create the PDFs 
                for i in range(0,len(cleaned_ai)):
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
                    
                    relative_pdf_path = os.path.join(f'{current_directory}/download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })
        print(f"out_ai : {out_ai}")
        print(f"cleaned_ai : {cleaned_ai}")
        print(f"results_with_pdfs : {results_with_pdfs}")
        return out_ai

    return render_template('index.html')
if __name__ == "__main__":
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)