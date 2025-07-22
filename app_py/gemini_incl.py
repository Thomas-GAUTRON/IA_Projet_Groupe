import os
from dotenv import load_dotenv
from langchain_google_genai import ChatGoogleGenerativeAI

current_directory = os.path.dirname(os.path.abspath(__file__))

'''
File for a class containing the AI agent via LangChain
'''
class AI:
    '''
    General Class for AI - Using Gemini 2.5 Flash
    '''
    def __init__(self,key = None,mod = "gemini-2.5-flash",temp=0):
        self.key = key
        if self.key is None:
            self.key = os.getenv("GOOGLE_API_KEY")
        self.model = mod
        self.temp = temp
        self.llm = None
        if not(self.key_setup()):
            self.model = False
            self.temp = self.model
        else:
            self.llm = ChatGoogleGenerativeAI(model = self.model,temperature = self.temp)
    
    def key_setup(self):
        '''
        Function to set-up the API key for the model
        '''
        if "GOOGLE_API_KEY" not in os.environ:
            print("Error in environment key, using .env instead")
            load_dotenv()
            self.key = os.getenv("GOOGLE_API_KEY")
            print(self.key)
            if type(self.key) != type("str"):
                print("The value in .env file is incorrect, the program will stop")
                return False
        return True

    def invoke_abs(self,sources,type = 0,edu = False):
        '''
        Function to ask the AI about the abstract. 
        Sources : array of string
        edu : boolean to ask for a more educational view of the abstract
        '''
        preamble =  r"""\documentclass[12pt]{article}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[margin=0.5in]{geometry}
\usepackage{amsmath, amssymb, amsfonts}
\usepackage{lmodern}
\usepackage{setspace}
\usepackage{needspace}
\usepackage[most]{tcolorbox}
\onehalfspacing
\usepackage{microtype}
\usepackage[table,xcdraw]{xcolor}
\usepackage{fancyhdr}
\pagestyle{fancy}
\fancyhf{}
\lhead{\textbf{AI Abstract}}
\rhead{\thepage}
\renewcommand{\headrulewidth}{0.4pt}
\renewcommand{\footrulewidth}{0pt}
\usepackage{parskip}
\author{DP,FA,GT}
\begin{document}

"""
        postamble = r"""\end{document}
"""
        if edu:
            prompt = "You are an expert teaching engine specialised in creating educational content. Your task is to create " \
            "a comprehensive and coherent abstract that allows for easy learning and comprehension in the most pedagogical way possible. " \
            "It should synthesizes the key information, main arguments, findings, and conclusions presented in the cortex of text provided." 
        else:
            prompt= "You are an expert academic summarizer and synthesis engine. Your task is to create a comprehensive " \
            "and coherent abstract that synthesizes the key information, main arguments, findings, and conclusions presented " \
            "in the cortex of text provided."

        input_specs="**Input:** You will be provided with an array of string representing the text of all " \
        "source document. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and have its index."

        ex_input ="**Example of Input Format in each index** : ---SOURCE_START---[Full text of Source 1]---SOURCE_END---"

        output_specs = "**Output Requirements:** 1**Content:** *Identify the overarching theme, or central topic addressed " \
        "by the sources. *Synthesize the primary arguments, methodologies, and key findings or insights from the sources. "\
        "*Conclude with the overall implications, a summary of the collective insights, or the main takeaway from the combined information." \
        "Do not hesitate to include formulas and important math relations or terms of equasions if considered important" \
        "2.**Format & Style:** *The abstract should be organized multiple coherent paragraphs, with seamless transitions, "\
        "following the organization of the source. *Aim for a length of approximately **1 A4 page recto-verso** but you can go longer"\
        "Do not hesitate to use markups to emphesize important concepts, points or ideas (acronyms, math formulas, etc)" \
        "Do not hesitate to include formulas and important math relations or terms of equasions if it is considered important" \
        "*Maintain an objective tone. *Avoid direct quotes unless for conveying a specific concept. *Do not include " \
        "citations or references within the abstract itself. *Do not introduce the sources individually (e.g., 'Source 1 states...', " \
        "'According to the second text...'). Instead, integrate the information seamlessly as if it were from a single, unified study." \
        "*Consider all sources as one single batch of information and do not present each source seperatly" \
        "*Language : Use the same language as the input source (i.e : source in french, write the summary in french)" \
        "3.**Result Format** **IMPORTANT**: *The result is to be written in LaTeX using utf-8 encoding and **IMPERATIVELY** using this "\
        "preambule and postambule given.*" \
        "The result must be encapsulated by : ---ABSTRACT START--- `abstract with provided preamble and postamble` "\
        "---ABSTRACT END--- allowing for a easier post-treatment."
         
        if(type): # Documents treated seperatly
            output_specs = "**Output Requirements:** 1**Content:** *Identify the overarching theme, or central topic addressed " \
            "from and for each source. *Synthesize the primary arguments, methodologies, and key findings or insights from each source. "\
            "*Conclude with the overall implications, a summary of the collective insights, or the main takeaway for each source." \
            "2.**Format & Style:** *The abstract should be organized in coherent paragraphs, with seamless transitions, following the "\
            "organization of the source. *Aim for a length of approximately **1 A4 page recto-verso** but you can go longer" \
            "Do not hesitate to use markups to emphesize important concepts, points or ideas (acronyms, math formulas, etc)" \
            "Do not hesitate to include formulas and important math relations or terms of equasions if it is considered important" \
            "*Maintain an objective tone. *Avoid direct quotes unless for conveying a specific concept. *Do not include " \
            "citations or references within the abstract itself. *Do not introduce the sources individually (e.g., 'Source 1 states...', " \
            "'According to the second text...') as it is repetitive. **Consider analysing and summarising each source independantly**." \
            "*Language : Use the same language as the input source (i.e : source in french, write the summary in french)" \
            "3.**Result Format** **IMPORTANT**: *The result is to be written in LaTeX using utf-8 encoding and **IMPERATIVELY** using the "\
            "preambule and postambule given.*" \
            "The result must be encapsulated by : ---ABSTRACT START--- `abstract with provided preamble and postamble` "\
            "---ABSTRACT END--- allowing for a easier post-treatment."

            ex_output = "**Exemple of Output Format : ---ABSTRACT START--- `abstract of Source 1 with imposed preamble and postamble` "\
            "---ABSTRACT END--- ---ABSTRACT START--- `abstract of Source 2 with imposed preamble and postamble` ---ABSTRACT END---"

            result = self.llm.invoke(f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\n{ex_output}\nPreamble : {preamble}\nPostamble : {postamble}\nSources : {sources}")
            return result.content
        result = self.llm.invoke(f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\nPreamble : {preamble}\nPostamble : {postamble}\nSources : {sources}")
        return result.content
        

    def invoke_quiz(self,sources,type = 0):
        '''
        Function to ask the AI to generate a quiz. 
        Sources : array of string
        '''
        prompt = "You are an expert academic engine specialised in quiz creation. Generate a multiple-choice quiz " \
        "based on the following array of strings, in which each index is the text of a source, while using the same language as the sources."

        input_specs="**Input:** You will be provided with an array of string representing the text of all " \
        "source document. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and have its index." \
        "Exemple : ---SOURCE_START---[Full text of Source 1]---SOURCE_END---"

        quiz_param = f"**Quiz Parameters** : 1. Number of questions : between 20 and 50 depending on the need." \
        f"2. Proposition per Questions : Alternating between 2 and 4 depending on the need of the question. " \
        f"3. Distractor Quality : Ensure incorrect options (distractors) are plausible but clearly incorrect." \
        "They should ideally be derived from the source material (e.g., misinterpretations, details from other " \
        "parts of the text) or common misconceptions related to the topic, rather than obviously wrong or irrelevant." \
        "4. Question Types : Vary the question types to test different aspects of understanding (e.g., factual recall, " \
        "inferential reasoning, understanding of key concepts, identification of main ideas, vocabulary)." \
        "5. Tone : Maintain a clear, concise, and objective tone."  \
        "6. For Maths and Special Characters : Use LaTeX to write math and special characters (e.g : \\omega, \\sqrt{2}, \\emptyset, etc.)"

        output_format="**Output Format:** The output must be a format must be saved directly as a json file, encapsulated by ---QUIZ_START--- and ---QUIZ_END---. " \
            "The format of your json response must be like the following :"

        ex_json_file=""
        with open(f"{current_directory}/ex.json", "r", encoding="utf-8") as f:
            ex_json_file = f.read()

        if type == 1:
            add = "**Output Format:** Create one quiz for each source file given with each file encapsulated by ---QUIZ_START--- and ---QUIZ_END--- like : "\
                "with the format like : ---QUIZ_START--- [quiz of Source 1 in json] ---QUIZ_END--- ---QUIZ_START--- [quiz of Source 2 in json] ---QUIZ_END---**" \
                "The format of your json files must be like the following : "
            result = self.llm.invoke(f"{prompt}\n{input_specs}\n{sources}\n{quiz_param}\n{add}\n{ex_json_file}")
            return result.content
        result = self.llm.invoke(f"{prompt}\n{input_specs}\n{sources}\n{quiz_param}\n{output_format}\n{ex_json_file}")
        return result.content
    

def translate(choice):
    '''
    Small function to transform a string choice from the form into a boolean
    '''
    if choice == "mtpl" or choice == "educational": # Multiple abstract and educational
        return True
    else:# One document for all and professional
        return False
