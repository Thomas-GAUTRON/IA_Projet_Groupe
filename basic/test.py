import classes as cs
fichier = cs.FSource("chapitre1.pdf","C:/apps/xampp/htdocs/SummerSchool/basic/uploads")
fichier1 = fichier
sources = cs.Source([fichier,fichier1],"2")
result = cs.Result(sources)
result.f_results = result.to_dict()
print(fichier)
print(fichier1)
print(sources)
print(result)